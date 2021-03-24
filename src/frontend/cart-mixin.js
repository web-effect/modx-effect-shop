const DEV = !!(process.env.NODE_ENV === 'development');

const data = {
    status: 'loading',
    cart: {
        qty: 0,
        items: {}
    },
    methods: {},
    form: {},
    lastOrder: {},

    loading: {
        cart: true,
        form: false,
    },
    error: '',
    
    favoritesCount: 0,
    compareCount: 0,
};

const mixin = {
    data,
    delimiters: ['(#', '#)'],
    methods: {
        request(action, data = {}, callback) {
            this.loading.cart = true;

            this.$shop.http('cart', action, data)
                .then((response) => {
                    response[0] != 1 && alert('Ошибка: корзина не загружена.');
                    this.cart = response.cart;
                    if (callback) callback();
                    
                    this.loading.cart = false;
                    this.status = this.cart.qty ? 'default' : 'empty';

                    const event = new CustomEvent('shop-cart-request', {
                        detail: { action, data, response }
                    });
                    document.dispatchEvent(event);
                    DEV && console.log(action, data, response);
                });
        },

        remove(index) {
            if (confirm('Удалить товар из корзины?')) {
                this.request('remove', { index });
            }
        },
        
        qty(index, plus) {
            const qty = +this.cart.items[index].qty + plus;
            if (qty < 0) return;
            if (qty === 0) {
                this.remove(index);
                return;
            }
            this.cart.items[index].qty = qty;
            this.request('qty', { index, qty });
        },

        setValue(key, val) {
            this.request('setValue', { key, val });
            DEV && console.log(key, val);
        },

        addonQty(index, addon, plus) {
            const a = this.cart.items[index].addons[addon];
            const qty = +a.qty + plus;
            if (qty < 0) return;
            a.qty = qty;
            this.request('addonQty', { index, addon, qty });
        },
        
        clean() {
            confirm('Очистить корзину?') && this.request('clean');
        },

        indexById(id) {
            let i = -1;
            if (this.cart.items) {
                i = this.cart.items.findIndex((a) => a.id == id);
            }
            return i;
        },

        sendForm() {
            this.loading.form = true;
            this.formError = '';

            this.$shop.http('order', 'sendForm', this.form)
                .then((data) => {
                    DEV && console.log(data);
                    if (data[0]) {
                        this.status = 'success';
                        this.cart = { items: {}, qty: 0 };
                        this.form.comment =  this.form.message = '';
                        this.lastOrder = data[1];	
                        window.scrollTo({ top: 0 });	
                        const event = new CustomEvent('shop-cart-order', {
                            detail: { order: this.lastOrder }
                        });
                        document.dispatchEvent(event);
                    } else {
                        this.error = data || 'Ошибка';
                    }
                    this.loading.form = false;
                });
        },

        getProductForm(el) {
            const out = {};
            const form = el.tagName == 'FORM' ? el : el.closest('form');
            if (!form) return false;

            out.form = form;
            out.button = form.querySelector('.shop-item-button');

            out.id_el = form.querySelector('[name=id]');
            out.qty_el = form.querySelector('[name=qty]');
            out.opt_els = form.querySelectorAll('[name^=opt-]') || [];
            out.addon_qty_els = form.querySelectorAll('[name^=addon-qty-]');
            out.addon_els = form.querySelectorAll('[name=addon]');
            out.variation_el = form.querySelector('[name=variation]:checked'); // вариативный, радиокнопки
            out.price_el = form.querySelector('[data-price]');

            out.id = +out.id_el.value;
            out.qty = out.qty_el ? +out.qty_el.value : 1;
            
            out.params = { opts: {}, addons: {} };

            if (out.variation_el) {
                out.variation = out.variation_el.value || '';
                out.variation_price = out.variation_el.dataset.variationPrice || 0;
                out.params.variation = out.variation
            }

            out.addon_els.forEach((el) => {
                if (!isNaN(parseInt(el.value))) {
                    if (el.type && ['checkbox', 'radio'].includes(el.type) && el.checked) {
                        out.params.addons[+el.value] = 1;
                    }
                } 
            })
            out.addon_qty_els.forEach((el) => {
                const name = el.name.split('-');
                out.params.addons[name[2]] = +el.value;
            })

            out.opt_els.forEach((opt) => {
                if (opt.value) out.params.opts[opt.name] = opt.value;
            })

            return out;
        }
    },
    
    computed: {
        plural() {
            const n = this.cart.qty,
                  cases = [2, 0, 1, 1, 1, 2],
                  words = ['товар', 'товара', 'товаров'];
            return words[(n%100>4 && n%100<20) ? 2 : cases[(n%10<5)?n%10:5]];  
        },
        
        cartClasses() {
            return [
                { 'is-loaded': this.status != 'loading' },
                { 'is-loading': this.loading.cart },
                { 'is-empty': !this.cart.qty },
                { 'not-empty': !!this.cart.qty }
            ]
        }
    },
    
};


export default mixin;