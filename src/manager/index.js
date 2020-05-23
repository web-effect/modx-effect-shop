import Vue from 'vue';
import VueRouter from 'vue-router';
import Buefy from 'buefy';
import 'buefy/dist/buefy.min.css';

Vue.use(VueRouter);
Vue.use(Buefy, {
	defaultFieldLabelPosition: 'inside',
	defaultFirstDayOfWeek: 1,
	defaultDayNames: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	defaultDatepickerNearbySelectableMonthDays: true,
})

import List from './list.vue';
import Order from './order.vue';
import Sets from './settings.vue';
import Index from './index.vue';

const routes = [
    { path: '', redirect: '/list' },
    { path: '/list', component: List, props: true },
    { path: '/list/:id', component: Order },
    { path: '/settings/:tab?', component: Sets }
];
  
const router = new VueRouter({ routes });


Vue.prototype.$url = '/assets/components/effectshop/mgr/connector.php';

Vue.prototype.$fd = function(obj, form, namespace) {
	let fd = form || new FormData();
	let formKey;
	for (let property in obj) {
		if (obj.hasOwnProperty(property) && obj[property]) {
			formKey = namespace ? namespace + '[' + property + ']' : property;
			if (typeof obj[property] === 'object' && !(obj[property] instanceof File)) {
				this.$fd(obj[property], fd, formKey);
			} else { // if it's a string or a File object
				fd.append(formKey, obj[property]);
			}
		}
	}
	return fd;
},

Vue.prototype.$http = function(to, action, body = {}) {
	const formData = this.$fd(body);

	return new Promise((resolve, reject) => {
		fetch(this.$url + `?to=${to}&action=${action}`, {
			method: 'post',
			body : formData,
		})
			.then(response => response.json())
			.then((data) => {
				resolve(data)
			})
			.catch((e) => {
				console.log(`Ошибка: ${e}`);
			});
	})
};


Vue.mixin({
    filters: {
        price(val) {
			val = parseFloat(val).toFixed(2);
            val = String(val).replace(/(\d)(?=(\d{3})+([^\d]|$))/g, "$1\u00a0");
            return val;		
		}
	},

	created() {
		if (this.$root.loaded) {
            this.sets = this.$root.info.sets;
        } else {
            this.$root.$on('loaded', (data) => this.sets = data.sets);
        }
	}
})


new Vue({
	render: h => h(Index),
	router,
	data: {
		settings: {},
		info: {},
		loaded: false,
    },
	created() {
		this.$http('shop', 'mgrLoad')
			.then((data) => {
				this.info = data;
				this.loaded = true;
				this.$emit('loaded', data);
				console.log(data);
			});
	},
}).$mount('#app');
