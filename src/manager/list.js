const params = new URLSearchParams(window.location.search.slice(1));

export default {
	
	data() { return {
		rows: [],
		sets: {},
		columns: [
			{ name: 'date', label: 'Номер, дата', width: 150 },
			{ name: 'status', label: 'Статус' },
			{ name: 'payment', label: 'Оплата' },
			{ name: 'delivery', label: 'Доставка' },
			{ name: 'total_price', label: 'Сумма', numeric: true },
			{ name: 'contacts__email', label: 'Email' },
		],
		loading: true,
		checked: [],

		page: 1,
		total: 0,
		limit: 25,

		filter: {
			page: 1,
			sortField: 'date',
			sortDir: 'DESC',
			dates: [],
		},
	}},
	
	methods: {
		paginate(val) {
            this.addFilter('page', val);
			window.scrollTo({ top: 0, behavior: "smooth" });
        },

        sorting(val) {
			this.filter.sortDir = this.filter.sortDir == 'ASC' ? 'DESC' : 'ASC';
            this.filter.sortField = val;
            this.addFilter();
        },

        addFilter(name, val, lazy = false) {
            this.timer && clearTimeout(this.timer);

            this.filter[name] = val;
            if (name !== 'page') this.filter.page = 1;

            this.timer = setTimeout(() => {
                this.request(1);
            }, lazy ? 500 : 0);
        },

        cleanFilter() {
            const filter = Object.assign({}, this.filter);
			Object.keys(filter).forEach(key => (!filter[key] || (typeof(filter[key]) == 'object' && !filter[key].length)) && delete filter[key]);
            return filter;
		},
		
		toUrl(filter) {
			Object.keys(filter).forEach((key) => {
				if (Array.isArray(filter[key])) {
					filter[key] = filter[key].join(',')
				}
			});
			this.$router.replace({ path: '/list', query: filter });
		},

		request(toUrl) {
			const filter = this.cleanFilter();
			this.loading = true;
			this.$http('order', 'getAll', filter)
			.then((data) => {
				this.rows = data.rows || [];
				this.total = data.total;
				this.limit = data.limit;
				this.loading = false;
				toUrl && this.toUrl(filter);
				console.log(data);
			});
		},
		
		setDates(val) {
            this.addFilter('dates', [+val[0]/1000, +val[1]/1000]);
        },

        getDates(val) {
            if (!val.length) return [];
            return [new Date(val[0]*1000), new Date(val[1]*1000)];
		},
		
		remove() {
			this.$buefy.dialog.confirm({
                message: 'Удалить выбранные заказы?',
                onConfirm: () => {
					this.loading = true;
					this.$http('order', 'remove', { ids: this.checked })
					.then((data) => {
						this.checked = [];
						this.request();
						console.log(data);
					});
				},
                cancelText: 'Отмена'
            });
		}
	},
	
	created() {
		if (this.$route.query) {
			for (let q in this.$route.query) {
				this.filter[q] = q == 'dates' ? this.$route.query[q].split(',') : this.$route.query[q];
			}
			if (this.$route.query.page) this.page = +this.$route.query.page;
		}
		this.request();
	}
	
};