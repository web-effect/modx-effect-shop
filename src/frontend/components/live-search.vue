<template>
	<div ref="wrapper" class="search"
		:class="{'is-loading': loading, 'is-expanded': show}"
	>
		<div class="search-field field">
			<input class="search-input input" type="search"
				v-bind="$attrs"
				@focus="focus()"
				v-model.trim="value"
				autocomplete="off"
			>
			<div class="field-icon"></div>
		</div>
		

		<div v-if="show" class="search-lists">

			<template v-if="products.length">
				<div class="search-list-title">Товары</div>
				<ul class="search-list">
					<li v-for="row in products" class="search-item">
						<a class="search-item-link" :href="row.uri">
							{{ row.name }}
						</a>
					</li>
				</ul>
			</template>

			<template v-if="categories.length">
				<div class="search-list-title">Категории</div>
				<ul class="search-list">
					<li v-for="row in categories" class="search-item">
						<a class="search-item-link" :href="row.uri">
							{{ row.name }}
						</a>
					</li>
				</ul>
			</template>


			<div v-if="nothing" class="search-empty">Ничего не найдено</div>


		</div>

	</div>
</template>



<script>
const DEV = !!(process.env.NODE_ENV === 'development');
const params = new URLSearchParams(location.search.slice(1));

export default {

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

	inheritAttrs: false,
	props: {},

	methods: {
		focus() {
			this.show = !!(this.value.length > 2 && this.typed);
		},
		
		request(query) {
			this.$shop.http('catalog', 'liveSearch', { query })
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
}
</script>