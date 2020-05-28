const el = document.querySelector('.vue-shop-product');
const DEV = !!(process.env.NODE_ENV === 'development');

if (el) {
    const ShopQVApp = new Vue({
        el,
        delimiters: Vue.prototype.$shop.delimiters,

        data: {
            id: 0,
            loading: true,
            product: {}
        },

        watch: {
            id (val) {
                this.loading = true;
                val && this.$shop.http('catalog', 'getOneFull', { id: val })
                    .then((resp) => {
                        DEV && console.log('Товар', resp);
                        if (resp) {
                            this.product = resp;
                            this.loading = false;
                        } 
                    })
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