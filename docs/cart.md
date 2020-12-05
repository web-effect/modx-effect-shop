# Корзина

У товара должны быть tv price, image. Опционально: adds (таблица с доп. товарами), price_old.

## Опции товара
В форме товара `name='opt-$name' value='$value'`. Обязательны для выбора. Не имеют цены.


## Доп. товары
Берутся из migx-таблицы addons. Можно добавить в корзину несколько. Добавление пока не готово, только через api.

## Корзина с анимацией в шапке 
```html
<a class="vue-shop-cart minicart" :class="cartClasses" href="/cart" title="Оформить заказ">
    <transition name="bounce" mode="out-in">
        <div :key="cart.total_price" class="minicart-price"><b>(# cart.total_price || 0 #) ₽</b></div>
    </transition>
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
