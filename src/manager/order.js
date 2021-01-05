export default {
	data() { return {
		sets: {}, 

		loaded: false,
		order: [],
		id: 0,
		
		statusLoading: false,
		statusModal: false,
		statusText: '',
		statusFile: [],
		statusVal: '',
		
		saveLoading: false,
		
		//чтоб отображать, какие товары сохранены
		productsIds: [],
		productLoading: false,
		productFinded: [],
		productSelected: [],

		userLoading: false,
		userFinded: [],
	}},
	
	methods: {
		/*
		productSearch(query) {
			if(!query) return;
			this.productLoading = true;

			this.$http('catalog', 'liveSearch', { query, mode: 'products' })
				.then((data) => {
					console.log('liveSearch', data);
					this.productFinded = data.rows || [];
					this.productLoading = false;
				});
		},
		
		productAdd(ev) {
			if(!ev) return false;
			this.productLoading = true;
			
			let data = {
				id: ev.id,
				order: JSON.stringify(this.order)
			};

			SMfetch('productAdd', data, 'Товар добавлен')
			.then(response => {
				this.order.items = response;
				this.productLoading = false;
				this.productSelected = [];
			});

		},*/
		
		
		productRemove(index)
		{
			if(typeof(index)!=='number') return false;
			this.$buefy.dialog.confirm({
				message: 'Удалить '+this.order.items[index].name+'?',
				onConfirm: () => this.order.items.splice(index, 1),
				cancelText: 'Отмена'
			});	
		},

		
		changeStatus() {
			this.statusLoading = true;
			
			const data = {
				id: this.order.id,
				status: this.statusVal,
				comment: this.statusText,
			};
			
			this.$http('order', 'changeStatus', data)
				.then((data) => {
					data[0] && this.loadOrder(data[1]);
					this.$buefy.toast.open({
						message: data[0] ? 'Статус обновлён' : (data[1] || 'Ошибка'),
						type: data[0] ? 'is-success' : 'is-danger',
					});
					this.statusLoading = false;
					this.statusModal = false;
				});
		},
		
		
		saveOrder() {
			this.saveLoading = true;
			
			let data = {
				id: this.order.id,
				order: this.order
			};
			
			this.$http('order', 'update', data)
				.then((data) => {
					this.loadOrder(data[1]);
					this.$buefy.toast.open({
						message: data[0] ? 'Заказ обновлён' : (data[1] || 'Ошибка'),
						type: data[0] ? 'is-success' : 'is-danger',
					});
				});
				
		},
	

		loadOrder(data) {
			console.log('loadOrder', data);
			if (!data || typeof(data) !== 'object') {
				alert('Ошибка загрузки заказа');
				return false;
			}
			//Если пусто, делаем объектами, чтоб не было ошибок
			if(!Object.keys(data.options).length) data.options = {};
			if(!Object.keys(data.contacts).length) data.contacts = {};
			this.order = data;
		
			this.loaded = true;
			this.saveLoading = false;

			this.productsIds = [];
			data.items.forEach(p => {
				this.productsIds.push(p.id);
			});
		}
		
	},
	
	
	created() {
		const id = this.$route.params.id || 0;
		this.$http('order', 'getOne', { id })
			.then((data) => {
				this.loadOrder(data);
			});
	},
	

};