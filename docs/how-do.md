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

## Удаление поля формы после заказа
Здесь нужно было добавлять цену за кол-во приборов (поле cutlery). Заходим в корзину после заказа — cutlety автозаполняется, удаляем.
```js
document.addEventListener("shop-cart-order", function(e) {
    ShopCartApp.form.cutlery = '';
});
document.addEventListener("shop-cart-load", function(e) {
    ShopCartApp.form.cutlery = ShopCartApp.cart.cutlery || '';
});
```

## Прикрепить файлы к письмам
```php
switch ($modx->event->name) {
    case 'ShopOrderBeforeSendEmails':
        $order['files_path'] = MODX_BASE_PATH . 'assets/web/css/';
        $order['files'][] = 'reset.css';
        $modx->event->output($order);
        break;
}
```

## Скидка, если введён ИНН
```html
<input class="input" type="text" name="inn" v-model="form.inn" @change="setValue('inn', $event.target.value)">
```
```php
switch ($modx->event->name) {
    case 'ShopCartBeforeProcess':
        if (!empty($cart['inn'])) {
            $cart['discount'] = 10;
        }
        $modx->event->output($cart);
        break;
}	
```
Заходим в корзину, инн автозаполнился с предыдущего заказа, но в shop_cart на сервере его ещё нет — добавляем
```js
document.addEventListener("shop-cart-load", function(e) {
	if (ShopCartApp.$data.form.inn && !ShopCartApp.$data.cart.inn) {
		ShopCartApp.setValue('inn', ShopCartApp.$data.form.inn);
	}
});
```
