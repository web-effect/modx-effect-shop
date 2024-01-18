## Форма заказа
Должно быть внутри .vue-shop-cart
```html
<form @submit.prevent="sendForm()" action="" class="Cart-order form">
	<div class="row">
		<div class="col-md-6 col-xl-4 Cart-order-col">
			<ef-input required
				v-model="form.fullname"
				label="Ф. И. О."
			></ef-input>
			
			<ef-input required
				type="tel"
				v-model="form.phone"
				label="Контактный телефон"
			></ef-input>
	
			<ef-input required
				type="email"
				v-model="form.email"
				label="E-mail"
			></ef-input>
	
			<ef-input
				type="textarea"
				v-model="form.comment"
				label="Примечание к заказу"
			></ef-input>
		</div>

		<div class="col-md-6 col-xl-4 Cart-order-col">
			<div class="Cart-order-section">
				<div class="Cart-order-subtitle">Доставка</div>
				<template v-for="i in methods.delivery">
					<ef-radio
						:key="i.key"
						name="delivery" v-model="form.delivery"  :val="i.key"
					>(# i.label #)</ef-radio>
				</template>
				<div class="radio-required">
					<input required type="radio" value="" name="delivery">
					<div>Выберите способ доставки</div>
				</div>
			</div>
	
			<div class="Cart-order-section">
				<div class="Cart-order-subtitle">Оплата</div>
				<template v-for="i in methods.payment">
					<ef-radio
						:key="i.key"
						name="payment" v-model="form.payment"  :val="i.key"
					>(# i.label #)</ef-radio>
				</template>
				<div class="radio-required">
					<input required type="radio" value="" name="payment">
					<div>Выберите способ оплаты</div>
				</div>
			</div>
		</div>
	</div>

		

	<button class="form-button button"
		:class="{ 'is-loading': loading.form }"
	>
		<span>Оформить заказ</span>
	</button>
	

	
	<p v-if="error" class="form-error">(# error #)</p>

	<div class="form__privacy">
		Отправляя данные через форму, я соглашаюсь с <a class="link-nocolor" target="_blank" href="/privacy">Политикой компании по обработке персональных данных</a>
	</div>

</form>
```
