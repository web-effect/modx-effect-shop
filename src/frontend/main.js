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
        //resource: window.resource || {},
        delimiters: ['(#', '#)'],
        pathname: window.location.pathname,
    },

    methods: {
        toFormData(obj, form, namespace) {
            let fd = form || new FormData();
            let formKey;
            for (let property in obj) {
                if (obj.hasOwnProperty(property) && obj[property] !== 'undefined') {
                    formKey = namespace ? namespace + '[' + property + ']' : property;
                    // Пустой массив
                    if (Array.isArray(obj[property]) && !obj[property].length) {
                        fd.append(formKey, obj[property]);
                    }
                    if (typeof obj[property] === 'object' && !(obj[property] instanceof File)) {
                        this.toFormData(obj[property], fd, formKey);
                    } else { // if it's a string or a File object
                        fd.append(formKey, obj[property]);
                    }
         
                }
                
            }
            return fd;
        },

        http(to, action, body = {}) {
            const formData = this.toFormData(body);
            //formData.append('ctx', this.resource.ctx || 'web');

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
                        // alert(`Ошибка: ${e}`);
                    });
            })
        }
    },
});