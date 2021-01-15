## Js-события

* shop-cart-load (response) — корзина загружена
* shop-cart-request (action, data, response) — запрос на изменение корзины
* shop-cart-order (order) — после заказа
* shop-catalog-update (el) — Каталог обновлён

Пример:
```js
document.addEventListener("shop-cart-request", function(e) {
    console.log(e.detail)
});
```

## События для плагинов

* ShopCartBeforeProcess ($cart)
* ShopCartAfterProcess ($cart)
Плагин должен возвращать `$modx->event->output($cart);`

* ShopOrderBeforeSendEmails ($order)
Плагин должен возвращать `$modx->event->output($order);`
