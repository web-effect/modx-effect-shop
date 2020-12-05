# Как сделать

## Цена доставки
В форме заказа
```html
<template v-for="i in methods.delivery">
    <ef-radio
        @input="setValue('delivery', $event)"
        :key="i.key"
        name="delivery" v-model="form.delivery"  :val="i.key"
    >(# i.label #)</ef-radio>
</template>
```
В плагине 
```php
switch ($modx->event->name) {
	case 'ShopCartBeforeProcess':
		if (!empty($cart['delivery']) && $cart['delivery'] == 'post') {
			$cart['delivery_price'] = 111;
		}
		$modx->event->output($cart);
		break;
}
```

## Обновление lazy-load картинок после загрузки каталога
```js
var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
});
document.addEventListener("shop-catalog-update", function() {
	lazyLoadInstance.update()
});
```