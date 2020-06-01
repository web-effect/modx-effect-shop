## js-события

* shop-cart-load (response) — корзина загружена
* shop-cart-request (action, data, response) — запрос на изменение корзины
* shop-cart-order (order) — после заказа
* shop-catalog-update (el)
```js
document.addEventListener("shop-cart-request", function(e) {
    console.log(e.detail)
});
```