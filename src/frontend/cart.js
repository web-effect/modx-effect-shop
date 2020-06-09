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
 * @todo getProductForm()
 */
document.addEventListener('submit', (e) => {
    if (!e.target.matches('.shop-item form')) return;
    e.preventDefault();

    const form = e.target,
          button = form.querySelector('.shop-item-button'),
          qty_el = form.querySelector('[name=qty]'),
          id_el = form.querySelector('[name=id]'),
          id = +id_el.value,
          qty = qty_el ? +qty_el.value : 1,
          opt_els = form.querySelectorAll('[name^=opt-]') || [],
          //addons_els = form.querySelectorAll('[name=addons]:checked'),
          params = { opts: {} };

    //params.addons =  Array.from(addons_els).map(cb => cb.value);

    opt_els.forEach((opt) => {
        if (opt.value) params.opts[opt.name] = opt.value;
    })
    form.classList.remove("is-error");
    if (Object.values(params.opts).length !== opt_els.length) {
        form.classList.add("is-error");
        console.log('не выбраны опции')
        return;
    }

    button && button.classList.add("is-loading");
    
    ShopCartApp.request('add', { id, qty, params }, () => {
        button && button.classList.remove("is-loading");
        button && button.classList.add("is-added");
        form.classList.add("is-added");
        if (qty_el) qty_el.value = 1;
    });
}, false);



/* Кнопки плюс-минус */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.shop-item-plus, .shop-item-minus');
    if (!btn) return;
    e.preventDefault();

    const input = e.target.parentNode.querySelector('[name*=qty]'),
          x = btn.classList.contains('shop-item-minus') ? -1 : 1,
          val = (parseInt(input.value) || 0) + x;

    let min = input.hasAttribute('min') ? +input.min : 1;
    input.value = val > (min - 1) ? val : min;

    const event = new Event("change");
    input.dispatchEvent(event);
});

document.addEventListener('blur', (e) => {
    if (!e.target.matches('.shop-item [name*=qty]')) return;
    let min = e.target.hasAttribute('min') ? +e.target.value.min : 1;
    if (!parseInt(e.target.value)) e.target.value = min;
}, true);



/* добавить в избранное или к сравнению */
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
