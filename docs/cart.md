# Корзина


## Корзина с анимацией в шапке 
```html
<a class="vue-shop-cart minicart" :class="cartClasses" href="/cart" title="Оформить заказ">
    <div class="minicart-text">
        Корзина: 
        <transition name="minicart-bounce" mode="out-in">
            <span :key="cart.qty" class="minicart-price">(# cart.qty #)</span>
        </transition>
    </div>
</a>
```
```css
.minicart:not(.is-loaded) {
    opacity: 0;
}
.bounce-enter-active {
    animation: bounce-in .5s;
}
@keyframes bounce-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.4);
    }
    100% {
        transform: scale(1);
    }
}
```

## Пример корзины
Должно быть внутри .vue-shop-cart

TODO: добавить подтовары

```html
<ul class="Cart-rows">
	<li v-for="item,n in cart.items" class="Cart-row">
		<a :href="item.url" :title="item.pagetitle" class="Cart-row-image">
			<img v-if="item.thumb" :src="item.thumb" alt="">
		</a>

		<div class="Cart-row-main">
			<a :href="item.url" :title="item.name" class="Cart-row-name link" v-html="item.name"></a>

			<div class="Cart-row-opts" v-if="item.options">
				<span v-for="val,name,i in item.options" class="Cart-row-opt">(# val #)</span>
			</div>
		</div>
		
		<div class="Cart-qty">
			<button @click="qty(n,-1)" class="">–</button>
			<input @change="qty(n,0)" v-model.number="item.qty" min=1 type="number" class="is-clear">
			<button @click="qty(n,+1)" class="">+</button>
		</div>

		<div class="Cart-row-prices">
			<div v-if="item.qty > 1" class="Cart-row-price-one">
			    За (# item.unit || 'шт.'  #): (# item.price | numFormat #) &#x20bd;
		    </div>
			<div class="Cart-row-price">(# item.total_price | numFormat #) &#x20bd;</div>
		</div>

		<button class="Cart-row-remove" @click="remove(n)" title="Удалить">×</button>
	</li>
</ul>

<div class="Cart-total box">
	Итоговая стоимость: <b> (# cart.total_price | numFormat #) &#x20bd;</b>
</div>
```

## Добавить / удалить подтовар по чекбоксу
```html
<ul>
	<li v-for="add,a in item.addons">
		<label>
			<input type="checkbox"
				@input="request('addonQty', { index:n, addon:a, qty: (add.qty ? 0 : 1) })"
				:checked="add.qty"
			>
			(# add.name #)
		</label>
	</li>
</ul>
```