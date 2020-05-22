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

Vue.prototype.$http = function(to, action, body = {}) {
	const formData = new FormData();

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








function SMtoast(data,text=':)') {
	if(!Array.isArray(data)) data = [];
	var ok = (data[0]==1) ? 1 : 0;
	SM.$buefy.toast.open({
		message: (ok ? text: (data[1] || ':(')),
		type: (ok ? 'is-success' : 'is-danger'),
		duration: 4000
	});
	if(!ok) console.log(data);
	return ok;
}


/*
Отправка запроса
Покажет оповещение при успехе или неуспехе
*/
function SMfetch(action, data, successText)
{
	let formData = new FormData();
	for(let i in data) {
		formData.set(i, data[i]);
	}
	
	return new Promise((resolve, reject) => {
		fetch(connector + '?action=' + action, {
			method: 'post', body: formData
		})
		.then(response => response.json())
		.then((data) => {
			let ok = SMtoast(data, successText);
			ok ? resolve(data[1]) : reject();
		});
	});
}