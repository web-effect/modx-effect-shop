import Cookies from 'js-cookie';
window.Cookies = Cookies;
window.Shop = window.Shop || {};


Vue.mixin({
    filters: {
        numFormat(val) {
			if(!val) return 0;
			val = parseFloat(val).toFixed(0);
            val = String(val).replace(/(\d)(?=(\d{3})+([^\d]|$))/g, "$1\u00a0");
            return val;		
		}
	},
})


Vue.prototype.$shop = new Vue({

	data: {
		url: '/assets/components/effectshop/connector.php',
		resource: window.resource || {},
		delimiters: ['(#', '#)'],
		pathname: window.location.pathname,
	},

	methods: {

		http(to, action, body = {}) {
			const formData = new FormData();
			formData.append('ctx', this.resource.ctx || 'web');

            for (let i in body) {
				if (body[i] instanceof FileList || Array.isArray(body[i])) {
					for (let x=0; x<body[i].length; x++){
						formData.append(`${i}[]`, body[i][x]) 
					}
				} else {
					if (typeof(body[i]) == 'object') {
						formData.append(i, JSON.stringify(body[i]));
					} else {
						formData.append(i, body[i]);
					}
				}
			}

			return new Promise((resolve, reject) => {
				fetch(this.url + `?to=${to}&action=${action}`, {
					method: 'post',
					body : formData,
				})
					.then(response => response.json())
					.then((data) => {
						resolve(data)
					})
					.catch((e) => {
						console.log(`Ошибка: ${e}`);
						alert(`Ошибка: ${e}`);
					});
			})
		}
		
	},
	
	created() {
		this.http('shop', 'load').then((data) => {
			data[0] == 1 ? this.$emit('load', data) : alert('Ошибка загрузки корзины')
		});
	}

});


/**
 * поиск с подсказками
 */
import ShopLiveSearch from './components/live-search.vue';
const searchNodes = document.querySelectorAll('.vue-shop-livesearch') || [];

searchNodes.forEach((el) => {
	new Vue({
		el,
		data: {},
		components: {
			ShopLiveSearch
		},
		delimiters: ['(#', '#)'],
	});
});