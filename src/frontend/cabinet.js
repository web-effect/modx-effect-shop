const el = document.querySelector('.vue-shop-cabinet');
const DEV = !!(process.env.NODE_ENV === 'development');
let hash = window.location.hash.split('/');


el && new Vue({
	el,
	delimiters: ['(#', '#)'],

	data: {
		user: {},

		tab: hash[1] || false,

		orderId: hash[2] || 0,
		orders: [],

		/*profile: {
			errors: {},
			form: {},
			loading: false,
			success: false,
		},

		password: {
			errors: {},
			form: { newpassword: 1 },
			loading: false,
			success: false,
		}*/
	},

	methods: {
		/*update(type = 'profile') {
			if (!['profile', 'password'].includes(type)) return;

			this[type].loading = true;
			this[type].success = false;
			this[type].errors = {};
			this.$shop.http('user', 'update', this[type].form)
				.then((resp) => {
					this[type].errors = resp.errors || {};
					this[type].loading = false;
					this[type].success = !!resp[0];
					if (this[type].success && type == 'password') {
						this[type].specifiedpassword = '';
						this[type].confirmpassword = '';
					}
					DEV && console.log(resp);
				})
		},*/

		cancelOrder() {
			if (confirm('Отменить заказ?')) {
				const data = {
					id: this.order.id,
					status: 'canceled',
					comment: 'Отменён пользователем'
				}
				this.$shop.http('order', 'changeStatus', data)
					.then((resp) => {
						this.$shop.http('order', 'getMyOrders')
							.then((resp) => {
								this.orders = resp.rows || [];
								DEV && console.log(resp);
							})
					})
			}
		}
		
	},

	computed: {
		order() {
			if (+this.orderId && this.orders.length) {
				window.location.hash = `/orders/${this.orderId}`;
				return this.orders.find((i) => i.id == this.orderId) || {};
			} 
			return {};
		},
	},

	created() {
		/*this.$shop.$on('load', (data) => {
			if (data.user) {
				this.user = data.user;
				this.profile.form = data.user;
			} 
		});*/

		this.$shop.http('order', 'getMyOrders')
			.then((resp) => {
				this.orders = resp.rows || [];
				DEV && console.log(resp);
			})

		window.onhashchange = () => {
			hash = window.location.hash.split('/');
			this.tab = hash[1] || 'orders';
			if (hash[1] == 'orders' && +hash[2]) {
				this.orderId = +hash[2];
			} else {
				this.orderId = 0;
			}
		};

		if (!this.tab) {
			this.tab = 'orders';
			window.location.hash = `/orders`;
		} 
	}
})
