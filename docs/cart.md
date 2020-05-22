# Корзина
```html
<!-- поправить -->
<div class="shop-cart-app">
	<div v-if="status == 'loading'">Загрузка...</div> 
	<div v-else-if="status == 'empty'">Корзина пуста</div> 
	<div v-else-if="status == 'success'">Спасибо за заказ</div> 

	<div v-else>
		<ul>
			<li v-for="item,n in cart.items">
				<img :src="item.thumb" alt="">
				<a :href="item.url" :title="item.pagetitle" class="cart__row-main">(# item.name #)</a>
				Цена за 1 шт. (# item.price | numFormat #) &#x20bd; <br>
				Сумма (# item.total_price | numFormat #) &#x20bd;
				<div>
					<button @click="qty(n,-1)">–</button>
					<input @change="qty(n,0)" v-model.number="item.qty" type="text">
					<button @click="qty(n,+1)">+</button>
				</div>
				<button @click="remove(n)" title="Удалить">×</button>
			</li>
		</ul>
		Итого <b>(# cart.total_price | numFormat #) &#x20bd;</b>

	</div> 
</div> 
```