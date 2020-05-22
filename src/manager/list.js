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
			{ name: 'contacts.email', label: 'Email' },
		],
		loading: false,
		total: 0,
		page: 1,
		filter: {
			sortField: 'id',
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
			this.filter.sortDir = this.filter.sortDir=='ASC' ? 'DESC' : 'ASC';
            this.filter.sortField = val;
            this.addFilter();
        },

        addFilter(name, val, lazy = false) {
            this.timer && clearTimeout(this.timer);

            this.filter[name] = val;
            if (name !== 'page') this.filter.page = 1;

            this.timer = setTimeout(() => {
                this.request();
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

		request(callback) {
			const filter = this.cleanFilter();
			this.loading = true;

			this.$http('order', 'getAll', filter)
				.then((data) => {
					this.rows = data.rows || [];
					this.total = data.total;
					this.loading = false;
					this.toUrl(filter);
					console.log(data);
					if(callback) callback();
				});
		},
		
		setDates(val) {
            this.addFilter('dates', [+val[0]/1000, +val[1]/1000]);
        },

        getDates(val) {
            if (!val.length) return [];
            return [new Date(val[0]*1000), new Date(val[1]*1000)];
        },
	},
	
	created() {
		if (this.$route.query) {
			for (let q in this.$route.query) {
				this.filter[q] = q == 'dates' ? this.$route.query[q].split(',') : this.$route.query[q];
			}
		}

		this.loading = true;
		this.$http('order', 'getAll')
			.then((data) => {
				this.rows = data.rows || [];
				this.total = data.total;
				this.loading = false;
				console.log(data);
			});

		
	}
	
};