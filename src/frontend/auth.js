const el = document.querySelector('.vue-shop-auth');
const DEV = !!(process.env.NODE_ENV === 'development');

el && new Vue({
	el,
	delimiters: ['(#', '#)'],

	data: {
		user: {},
		isAuth: false,

		login: {
			form: {},
			loading: false,
			message: '',
		},

		reg: {
			form: {},
			loading: false,
			message: '',
			errors: {},
			success: false,
		}
	},

	methods: {
		authorize() {
			this.login.loading = true;
			this.login.message = ''
			this.$shop.http('user', 'login', this.login.form)
                .then((resp) => {
					DEV && console.log(resp);
					this.login.loading = false;
					if (resp[0]) {
						location.reload();
					} else {
						this.login.message = resp.error || 'Ошибка входа'
					}
                });
		},

		register() {
			this.reg.loading = true;
			this.reg.errors = {};
			this.$shop.http('user', 'register', this.reg.form)
                .then((resp) => {
					this.reg.errors = resp.errors || {};
					this.reg.loading = false;
					this.reg.success = !!resp[0];
					DEV && console.log(resp);
                });
		},

		logout() {
			this.$shop.http('user', 'logout')
                .then((resp) => {
					DEV && console.log(resp);
					if (+resp[0]) {
						location.reload();
					} else {
						alert(resp.error || 'Ошибка');
					}
                });
		}
	},

	created() {
		this.$shop.$on('load', (data) => {
			if (data.user) {
				this.user = data.user;
				this.isAuth = !!+data.user.id;
			}
        });
	}
})