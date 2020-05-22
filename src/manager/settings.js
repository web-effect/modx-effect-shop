export default{
    data() {
    return {
        tab: 'statuses',
        tabs: {
            statuses: { label: 'Статусы' },
            payment: { label: 'Оплата' },
            delivery: { label: 'Доставка' },
        },
        sets: {},
        loading: false,
    }},

    methods: {
        saveSetting() {
			this.loading = true;
			
            const data = {
                key: this.$route.params.tab,
                value: JSON.stringify(this.sets[this.$route.params.tab])
			}
			console.log(data);

			this.$http('params', 'update', data)
				.then((data) => {
					this.$buefy.toast.open({
						message: data[0] ? 'Настройки сохранены' : (data[1] || 'Ошибка'),
						type: data[0] ? 'is-success' : 'is-danger',
					});
					this.loading = false;
					console.log(data);
				});
        },
        
        addRow() {
            const arr = this.sets[this.$route.params.tab];
            arr.push({ });
        },
        
        remove(index) {
            if (typeof(index)!=='number') return false;
            this.$buefy.dialog.confirm({
                message: 'Удалить?',
                onConfirm: () => this.sets[this.tab].splice(index, 1),
                cancelText: 'Отмена'
            });
        },

    },
    
    created() {
        if (!this.$route.params.tab) {
            this.$router.push({path: `/settings/statuses`})
        } 
        if (this.$root.loaded) {
            this.sets = this.$root.info.sets;
        } else {
            this.$root.$on('loaded', (data) => this.sets = data.sets);
        }
        
        console.log(this.sets);
    },
    
}