const DEV = !!(process.env.NODE_ENV === 'development'),
      appNodes = document.querySelectorAll('.shop-catalog-app') || [],
      catalogEl = document.querySelector('.shop-catalog-items'),
      dataEl = document.querySelector('.shop-catalog-data'),
      filtersDataEl = document.querySelector('[data-shop-filters]'),
      params = new URLSearchParams(location.search.slice(1));


if (appNodes.length) {

    let filtersData = {};
    if (filtersDataEl) {
        filtersData = filtersDataEl.dataset.shopFilters;
        filtersData = JSON.parse(filtersData);
    }

    const filters = filtersData.filters || {},
          filtersList = filtersData.list || [],
          filterForm = {},
          filterQuery = {};

    for (let f in filtersList) {
        filterForm[filtersList[f]] = [];
    }

    DEV && console.log('Фильтры', filtersData);

    params.forEach((val, key) => {
        if (key.includes('filter_')) {
            val = val.split(',');
            filterQuery[key] = val;
            filterForm[key.replace('filter_', '')] = val;
        }
        if (key.includes('min_')) {
            filterQuery[key] = val;
            key = key.replace('min_', '');
            if (filterForm[key]) filterForm[key][0] = val;
        }
        if (key.includes('max_')) {
            filterQuery[key] = val;
            key = key.replace('max_', '');
            if (filterForm[key]) filterForm[key][1] = val;
        }
    });


    let total, limit;
    if (catalogEl) {
        total = dataEl.dataset.total || 0;
        limit = dataEl.dataset.limit || 1;
    }


    const data = {
        total, limit,

        filters, //Значения фильтров
        filtersList, //Список фильтров
        filterForm: Object.assign({}, filterForm), //Поля для v-model
        filterQuery, //Поля для запросов
        isFiltered: !!Object.entries(filterQuery).length,
        filterSection: '',
        filterVisible: false,

        page: +params.get('page') || 1,
        view: Cookies.get('shop_view') || 'grid',
        sort: Cookies.get('shop_sort') || '',
    };


    const methods = {

        request(resetPage, showMore = false) {
            this.isFiltered = !!Object.entries(this.filterQuery).length;

            catalogEl.classList.add('is-loading');

            if (resetPage) this.page = 1;

            const data = {
                filter: Object.assign({}, this.filterQuery)
            }
            if (this.page > 1) data.filter.page = this.page;

            if (params.get('search')) {
                if (params.get('search')) data.filter.search = params.get('search');
            } else {
                data.filter.sort = this.sort;
            }

            const queryString = new URLSearchParams(data.filter);
            history.pushState(null, null, window.location.pathname + '?' + queryString);
            

            this.$shop.http('catalogSnippet', 'renderCatalog', data)
                .then((resp) => {
                    DEV && console.log(resp);

                    const newDiv = document.createElement("div");
                    newDiv.innerHTML = resp.html;
                    const itemsNode = newDiv.querySelector('.shop-catalog-items');
                    if (!showMore) catalogEl.innerHTML = "";
                    catalogEl.insertAdjacentHTML('beforeend', itemsNode.innerHTML);
                    
                    this.total = resp.total;
                    
                    catalogEl.classList.remove('is-loading');

                    const event = new CustomEvent('shop-catalog-update', {
                        detail: { el: catalogEl }
                    });
                    document.dispatchEvent(event);
                })
    
        },

        filter(name, type = 'checkboxes') {
            if (type == 'range') {
                this.filterQuery['min_' + name] = this.filterForm[name][0];
                this.filterQuery['max_' + name] = this.filterForm[name][1];
            } else {
                if (this.filterForm[name].length) {
                    this.filterQuery['filter_' + name] = this.filterForm[name];
                } else {
                    delete this.filterQuery['filter_' + name];
                }
            }
            this.request(true);
        },
        
        sorting() {
            this.request(true);
            Cookies.set('shop_sort', this.sort);
        },

        pagination(page) {
            this.request();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.filterVisible = false;
        },

        showMore() {
            if (this.page >= this.pages) return;
            this.page++;
            this.request(false, true);
        },

        reset() {
            //this.isFiltered = false;
            for (let f in this.filterForm) {
                this.filterForm[f] = [];
            }
            this.filterQuery = {};
            this.request(true);
            this.filterVisible = false;
        }

    };

    const computed = {
        pages() {
            return Math.ceil(this.total / this.limit);
        }
    };

    if (appNodes.length) {
        const ShopCatalogApp = new Vue({
            data,
            methods,
            watch: {
                view(v) {
                    const cl = catalogEl.classList;
                    const classes = catalogEl.className.split(' ');
                    classes.forEach((i) => i.includes('has-view-') && cl.remove(i))
                    cl.add(`has-view-${v}`);
                    Cookies.set('shop_view', v);
                },
            },
        });
    }


    appNodes.forEach((el) => {
        new Vue({
            el,
            data,
            methods,
            computed,
            delimiters: ['(#', '#)'],
        });
    });

}