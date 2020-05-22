const el = document.querySelector('.vue-shop-product');
const DEV = !!(process.env.NODE_ENV === 'development');

if (el) {
	const ShopQVApp = new Vue({
		el,
		delimiters: Vue.prototype.$shop.delimiters,

		data: {
			id: 0,
		},

		watch: {
			id(val) {
				if (val) {
					console.log('получение данных...');
				}
			}
		},


	})

	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.shop-item-qv');
		if (!btn) return;
		e.preventDefault();
		
		const productEl = e.target.closest('.shop-item'),
			  idEl = productEl.querySelector('[name=id]'),
			  id = idEl.value;


		ShopQVApp.id = id;
	});
	


}