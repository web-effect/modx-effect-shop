<style>
	textarea {
		min-height: 4.5rem !important;
	}
	.order input {
		max-width: 7em;
	}
	.order-add-item {
		margin-top: 10px;
		display: flex;
		align-items: center;
	}
	.order-add-item-name {
		flex-grow: 2;
	}
	.order-add-item .has-numberinput {
		margin-left: 10px;
	}
	.order-add-item .numberinput input {
		max-width: 3.5em;
	}
	.order-add-item .field {
		margin-bottom: 0 !important;
	}
	.list-item {
		margin-bottom: .75em;
	}
</style>

<template id="sm-order">
	<main class="container">
	<form class=""
		@submit.prevent="saveOrder()"
	>

		<div class="columns">
			<div class="column is-7">
			<div class="box">
				<ul class="list">
					<li class="list-item">
						<div class="level">
							<div>
								<b-button tag="router-link" icon-left="arrow-left"
									to="/"
								></b-button>
								<span class="title"> Заказ #{{ order.id }}</span>
							</div>
							<div>
								<b-button  @click="statusModal=true">Сменить статус</b-button>
								<b-button :loading="saveLoading" native-type="submit" type="is-primary">Сохранить изменения</b-button>
							</div>
							
						</div>
					</li>
					<li class="list-item">
						Статус: {{  order.status_name }}
						<span class="status-color" :style="{ background: order.status_color }"></span>
					</li>
					<li class="list-item">Дата: {{ order.date }}</li>
					<li class="list-item">Сумма заказа: {{ order.total_price }} руб.</li>
				</ul>
				
				<b-field label="Оплата">
					<b-select v-model="order.payment" expanded>
						<option
							v-for="option in sets.payment"
							:value="option.key"
						>
							{{ option.label }}
						</option>
					</b-select>
				</b-field>
				
				<div v-if="order.options && order.options.pay_error"
					class="notification is-danger"
				>
					{{ order.options.pay_error }}
				</div>

				<b-field label="Доставка">
					<b-select v-model="order.delivery" expanded>
						<option
							v-for="option in sets.delivery"
							:value="option.key"
						>
							{{ option.label }}
						</option>
					</b-select>
				</b-field>

				<b-field label="Стоимость доставки">
					<b-input v-model="order.delivery_price" type="number" min="0"></b-input>
				</b-field>
				
				<b-field label="Скидка, %">
					<b-input v-model="order.discount" type="number" min="0" max="99"></b-input>
				</b-field>
			</div>
			</div><!-- column -->
			
	
			<div class="column">
				<div class="box">
	
					<div class="title is-5">Контакты</div>
					
					<b-field v-for="label, name in order.contact_fields" :label="label" :key="name" :label-for="name">
	
						<template v-if="name=='email'">
							<b-input :id="name" required type="email" v-model="order.contacts[name]"></b-input>
						</template>
						<template v-else-if="['address', 'comment'].includes(name)">
							<b-input :id="name" type="textarea"  v-model="order.contacts[name]"></b-input>
						</template>
						<template v-else>
							<b-input :id="name" v-model="order.contacts[name]"></b-input>
						</template>
						
					</b-field>
	
				</div>
			</div><!-- column -->	
				
		</div><!-- columns -->
		
	
		<div class="box order">
			<div class="title is-4">Состав заказа</div>
				
			<b-table :data="order.items">
				<b-table-column field="name" label="Название"
					v-slot="props"
				>
					<a target="_blank" :href="props.row.url"
						:class="{opacity: !productsIds.includes(props.row.id)}"
						v-html="props.row.name"
					></a>
					
					<!-- Вариация -->
					<div v-if="props.row.variation && props.row.variations && props.row.variations[props.row.variation]">
						{{ props.row.variations[props.row.variation].name }}
						<div class="has-text-grey">
							<small>Цена вариативного товара не редактируется</small>
						</div>
					</div>

					<!-- Опции -->
					<ul v-if="props.row.options">
						<li v-for="opt in props.row.options" class="order-opt-item">
							<small v-html="opt"></small>
						</li>
					</ul>

					<!-- доп. товары -->
					<ul v-if="props.row.addons">
						<li v-for="add in props.row.addons" class="order-add-item">
							<div class="order-add-item-name"
								:class="{ 'has-text-grey-light' : !add.qty}"
								v-html="add.name"
							></div>
							
							<b-field>
								<b-input required type="number" v-model="add.initial_price" min="0.01" step="0.01"
									size="is-small"
								></b-input>
							</b-field>
							<b-field>
								<b-numberinput required v-model="add.qty"  min="0"
									controls-position="compact" type="is-light" size="is-small"
								></b-numberinput>
							</b-field>
						</li>
					</ul>
				</b-table-column>
				
				<b-table-column field="price" label="Цена, руб."
					v-slot="props"
				>
					<b-field>
						<b-input required type="number" v-model="props.row.initial_price"
							min="0.01" step="0.01"
						></b-input>
					</b-field>
				</b-table-column>
				
				<b-table-column field="qty" label="Кол-во" width="140" centered
					v-slot="props"
				>
					<b-field>
						<b-numberinput required  v-model="props.row.qty" type="is-light" min="1" controls-position="compact"></b-numberinput>
					</b-field>
				</b-table-column>

				<b-table-column  field="price" label="Сумма, руб." numeric
					v-slot="props"
				>
					{{ (props.row.price * props.row.qty) | price }}
					<div v-if="props.row.discount_percent">
						<small>−{{ props.row.discount_percent }}%</small>
					</div>
				</b-table-column>

				<b-table-column  custom-key="remove"
					v-slot="props"
				>
					<span @click="productRemove(props.index)" class="delete"></span>
				</b-table-column>
									
				<template slot="footer">
					<th colspan="4">
						<div class="has-text-right">
							{{ order.price | price }}
						</div>
					</th>
					<th></th>
				</template>
			</b-table>

			<!--  
			<b-field label="Добавить товар" class="add-product">
				<b-autocomplete icon="magnify"
					field="name"
					placeholder="Введите название товара"
					@select="productAdd($event)"
					@typing="productSearch"
					:data="productFinded"
					:loading="productLoading"
				>
					<template v-if="!productLoading" slot="empty">Нет результатов</template>
				</b-autocomplete>
			</b-field>
			-->
		</div><!-- box -->
		

	</form>
	
	<div class="level"></div>

	<div v-if="order.history" class="box">
				
		<div class="title is-4">История статусов</div>
		
		<b-table :data="order.history">				
			<b-table-column field="status" label="Статус"
				v-slot="props"
			>
				{{ props.row.status_name }}
			</b-table-column>
		
			<b-table-column field="date" label="Дата"
				v-slot="props"
			>
				{{ props.row.date }}
			</b-table-column>
			
			<b-table-column field="comment" label="Комментарий"
				v-slot="props"
			>
				{{ props.row.comment }}
			</b-table-column>
		</b-table>

	</div><!-- box -->
	
	
	<!-- СТАТУСЫ -->
	<b-modal :active.sync="statusModal" :has-modal-card="true">
	<form @submit.prevent="changeStatus($event)" class="modal-card" style="width: 500px" >
			
		<header class="modal-card-head">
			<p class="modal-card-title">Изменить статус заказа</p>
		</header>
	
		<div class="modal-card-body">
			<b-field v-if="$root.loaded">
				<b-select :value="order.status" @input="statusVal = $event">
					<option v-for="s in $root.info.sets.statuses" :value="s.key">{{ s.label }}</option>
				</b-select>
			</b-field>
			<b-field >
				<b-input v-model="statusText" type="textarea" placeholder="Комментарий"></b-input>
			</b-field>
		</div>
		
		<footer class="modal-card-foot">
			<b-button icon-left="content-save" type="is-primary" native-type="submit"
				:loading="statusLoading"
				:disabled="!statusVal"
			>
				Сохранить
			</b-button>
		</footer>
		
	</form>
	</b-modal>
	
	
	
</main>
</template>

<script src="./order.js"></script>