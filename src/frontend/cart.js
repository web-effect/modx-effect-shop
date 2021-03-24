const DEV = !!(process.env.NODE_ENV === 'development');
const appNodes = document.querySelectorAll('.vue-shop-cart') || [];

import mixin from './cart-mixin.js';

const ShopCartApp = new Vue({
    
    mixins: [mixin],
    
    created() {
        if (localStorage.getItem('shop_order_form')) {
            try {
                this.form = JSON.parse(localStorage.getItem('shop_order_form'));
            } catch(e) {
                localStorage.removeItem('shop_order_form');
            }
        }

        this.$shop.$on('load', (data) => {
            this.cart = data.cart;
            this.methods = data.methods;
            this.status = this.cart.qty ? 'default' : 'empty';
            this.loading.cart = false;
            DEV && console.log('Корзина', data);
            if (data.user) {
                for (let field in data.user) {
                    if (data.user[field] && typeof(data.user[field]) !== 'object') {
                        this.form[field] = data.user[field];
                    }
                }
            }
            const event = new CustomEvent('shop-cart-load', {
                detail: { response: data }
            });
            document.dispatchEvent(event);
        });
        
        this.favoritesCount = savedProductsFromCookie('favorites').length;
        this.compareCount = savedProductsFromCookie('compare').length;
    },

    watch: {
        form: {
            handler() {
                const o = Object.assign({}, this.form);
                o.delivery && delete o.delivery;
                o.payment && delete o.payment;
                localStorage.setItem('shop_order_form', JSON.stringify(o));
            },
            deep: true
        }
    }
});
window.ShopCartApp = ShopCartApp;


appNodes.forEach((el) => {
    new Vue({
        el, mixins: [mixin],
    });
});



/**
 * Добавление в корзину
 */
document.addEventListener('submit', (e) => {
    if (!e.target.matches('.shop-item form')) return;
    e.preventDefault();

    const f = ShopCartApp.getProductForm(e.target);

    f.form.classList.remove("is-error");
    if (Object.values(f.params.opts).length !== f.opt_els.length) {
        f.form.classList.add("is-error");
        console.log('не выбраны опции');
        return;
    }

    f.button && f.button.classList.add("is-loading");
    
    ShopCartApp.request('add', { id: f.id, qty: f.qty, params: f.params }, () => {
        f.button && f.button.classList.remove("is-loading");
        f.button && f.button.classList.add("is-added");
        f.form.classList.add("is-added");
        f.form.reset();
        f.form.dispatchEvent(new Event("change"));
    });
}, false);


/**
 * Считаем цену
 */
document.addEventListener('change', (e) => {
    const form = e.target.closest('.shop-item form');
    if (!form) return;
    
    const f = ShopCartApp.getProductForm(e.target);
    if (!f.price_el) return;

    let price = +f.price_el.dataset.price;

    if (f.variation_price) {
        price = f.variation_price;
    }

    /** если меняется кол-во подтоваров */
    f.addon_qty_els && f.addon_qty_els.forEach((el) => {
        if (el.dataset.addonPrice) {
            price += +el.value * +el.dataset.addonPrice;
        }
    })
    /** если подтовары чекбоксами или радиокнопками */
    const prices_plus_els = f.form.querySelectorAll('[data-price-plus]');
    prices_plus_els && prices_plus_els.forEach((el) => {
        if (el.type && ['checkbox', 'radio'].includes(el.type) && el.checked) {
            price += +el.dataset.pricePlus;
        }
    })

    price *= f.qty;

    f.price_el.innerHTML = price;
    DEV && console.log(price);

}, true);


/**
 * Кнопки плюс-минус
 */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.shop-item-plus, .shop-item-minus');
    if (!btn) return;
    e.preventDefault();

    const input = e.target.parentNode.querySelector('[name*=qty]'),
          x = btn.classList.contains('shop-item-minus') ? -1 : 1,
          val = (parseInt(input.value) || 0) + x;

    let min = input.hasAttribute('min') ? +input.min : 1,
        max = input.hasAttribute('max') ? +input.max : 999;

    if (val < max + 1 && val > min - 1) {
        input.value = val;
        input.dispatchEvent(new Event("change"));
    }
});

document.addEventListener('blur', (e) => {
    if (!e.target.matches('.shop-item [name*=qty]')) return;

    let min = e.target.hasAttribute('min') ? +e.target.min : 1,
        max = e.target.hasAttribute('max') ? +e.target.max : 999;

    if (!parseInt(e.target.value)) e.target.value = min;
    if (e.target.value > max) e.target.value = max;
    if (e.target.value < min) e.target.value = min;

    e.target.dispatchEvent(new Event("change"));
}, true);


/**
 * добавить в избранное или к сравнению
 */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.shop-item-to-favorites');
    if (!btn) return;
    e.preventDefault();

    let name = 'favorites';
    const productEl = e.target.closest('.shop-item'),
          idEl = productEl.querySelector('[name=id]'),
          id = idEl.value,
          catalogDataEl = document.querySelector('.shop-catalog-data'),
          pageType = catalogDataEl ? catalogDataEl.dataset.pageType : '';

    var ids = savedProductsFromCookie(name),
        index = ids.indexOf(id);

    if (index > -1) {
        ids.splice(index, 1);
        btn.classList.remove('is-active');
        if (pageType == name) productEl.remove();
        DEV && console.log(`${id} удалён из ${name}`);
    } else {
        ids.push(id);
        btn.classList.add('is-active');
        DEV && console.log(`${id} добавлен в ${name}`);
    }

    ShopCartApp[name + 'Count'] = ids.length;
    Cookies.set('shop_' + name, ids);
});


function savedProductsFromCookie(name) {
    var ids = Cookies.get('shop_' + name) || {};
    try {
        ids = JSON.parse(ids);
    } catch (e) {
        ids = [];
    }
    if (!Array.isArray(ids)) ids = [];
    
    return ids;
}
