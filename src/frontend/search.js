/**
 * поиск с подсказками
 */
const DEV = !!(process.env.NODE_ENV === 'development');
const searchNodes = document.querySelectorAll('.vue-shop-livesearch') || [];
const params = new URLSearchParams(location.search.slice(1));

searchNodes.forEach((el) => {
    new Vue({
        el,
        delimiters: ['(#', '#)'],

        data() {
            return {
                products: [],
                categories: [],
                value: params.get('search') || '',
                show: false,
                timer: null,
                loading: false,
                nothing: false,
                typed: false, // печаталось ли что-то
            }
        },
    
        methods: {
            focus() {
                this.show = !!(this.value.length > 2 && this.typed);
            },
            
            request(query) {
                // Без encodeURIComponent проблема с плюсом в поиске
                this.$shop.http('catalog', 'liveSearch', { query: encodeURIComponent(query) })
                    .then((resp) => {
                        DEV && console.log(resp);
                        const rows = resp.rows || [];
                        this.show = true;
                        this.nothing = !rows.length;
                        this.loading = false;
                        this.products = rows.filter((v) => v.type == 'product');
                        this.categories = rows.filter((v) => v.type == 'category');
                    })
            },
    
            documentClick(e) {
                const el = this.$refs.wrapper,
                      target = e.target;
                if (el !== target && !el.contains(target)) {
                    this.show = false;
                }
            },
        },
    
        watch: {
            value(val) {
                this.typed = true;
    
                if (!val || val.length <= 2) {
                    this.show = false;
                    return;
                };
                
                this.loading = true;
                this.timer && clearTimeout(this.timer);
                this.timer = setTimeout(this.request, 500, val);
            }
        },
    
        created() {
            document.addEventListener('click', this.documentClick)
        },
        destroyed() {
            document.removeEventListener('click', this.documentClick)
        },
    });
});