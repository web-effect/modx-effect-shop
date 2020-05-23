const DEV = !!(process.env.NODE_ENV === 'development');
const appNodes = document.querySelectorAll('.vue-shop-cart') || [];

/* общие данные для всех эксемпляров shop-app */
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

		request(action, data, callback) {
			this.loading.cart = true;

            this.$shop.http('cart', action, data)
                .then((response) => {
                    response[0] != 1 && alert('Ошибка: корзина не загружена.');
                    this.cart = response.cart;
					if (callback) callback();
					
					this.loading.cart = false;
					this.status = this.cart.qty ? 'default' : 'empty';

					const event = new CustomEvent('shop-cart-' + action, {
						detail: { data, response }
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
			if (!qty) return;
            this.cart.items[index].qty = qty;
            this.request('qty', { index, qty });
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
                    } else {
                        this.error = data || 'Ошибка';
                    }
					this.loading.form = false;
					window.scrollTo({ top: 0 });

					const event = new CustomEvent('shop-cart-order', {
						detail: { order: this.lastOrder }
					});
					document.dispatchEvent(event);
                });
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


appNodes.forEach((el) => {
	new Vue({
		el, mixins: [mixin],
	});
});



/* Добавление в корзину */

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
		  options = {};

	opt_els.forEach((opt) => {
		if (opt.value) options[opt.name] = opt.value;
	})

	form.classList.remove("is-error");

	if (Object.values(options).length !== opt_els.length) {
		form.classList.add("is-error");
		console.log('не выбраны опции')
		return;
	}

    button && button.classList.add("is-loading");
	
	ShopCartApp.request('add', { id, qty, options }, () => {
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

	const input = e.target.closest('.shop-item').querySelector('[name=qty]'),
		  x = btn.classList.contains('shop-item-minus') ? -1 : 1,
		  val = (parseInt(input.value) || 0) + x;
	input.value = val > 0 ? val : 1;
});

document.addEventListener('blur', (e) => {
	if (!e.target.matches('.shop-item [name=qty]')) return;
	if (!parseInt(e.target.value)) e.target.value = 1
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


    // колонка товара для удаления на стр. избранного или сравнения
    // var productWrapper = $(this).closest('[class*=col-]');
    // секция на стр. избранного или сравнения
    // var sectionWrapper = $(this).closest('.products-' + name);

	

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
