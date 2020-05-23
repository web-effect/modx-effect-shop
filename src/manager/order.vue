<style>
	textarea {
		min-height: 4.5rem !important;
	}
</style>

<template id="sm-order">
	<main class="container">
	<form class=""
		@submit.prevent="saveOrder()"
	>

		<div class="columns">
			<div class="column is-7">
				
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
					<b-input v-model="order.discount" type="number" min="0" max="100"></b-input>
				</b-field>

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
		
	
		<div class="box">
			<div class="title is-4">Состав заказа</div>
				
			<b-table :data="order.items">
				<template slot-scope="props">
				
					<b-table-column field="name" label="Название" :class="{opacity: !productsIds.includes(props.row.id)}">
						<a target="_blank" :href="props.row.url">{{ props.row.name }}</a>
					</b-table-column>
					
					<b-table-column field="price" label="Цена, руб." numeric>
						{{ props.row.price | price }}
					</b-table-column>
					
					<b-table-column field="qty" label="Кол-во" width="180" centered>
						<b-field>
							<b-numberinput required  v-model="props.row.qty" type="is-light" min="1"></b-numberinput>
						</b-field>
					</b-table-column>
	
					<b-table-column  field="price" label="Сумма, руб." numeric>
						{{ (props.row.price * props.row.qty) | price }}
					</b-table-column>
	
					<b-table-column  custom-key="remove">
						<span @click="productRemove(props.index)" class="delete"></span>
					</b-table-column>
					
				</template>
				
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
			<template slot-scope="props">
			
				<b-table-column field="status" label="Статус">
					{{ props.row.status_name }}
				</b-table-column>
			
				<b-table-column field="date" label="Дата">
					{{ props.row.date }}
				</b-table-column>
				
				<b-table-column field="comment" label="Комментарий">
					{{ props.row.comment }}
				</b-table-column>
				
			</template>
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