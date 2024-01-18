В head прописать:
```html
{*тестовый (1) или боевой (0) режим. на тестовом включены sourcemaps, в консоль брвузера пишутся логи*}
{set $dev = 0}

{*скачиваем vue 2 версии, кладём куда хотим, подключаем*}
{*https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.js*}
{*https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js*}
{if $dev}
    {'assets/vendor/vue.js'|jsToBottom}
{else}
    {'/assets/vendor/vue.min.js'|jsToBottom}
{/if}

{*скачиваем nouislider, кладём куда хотим, подключаем. если не нужен ползунок в фильтрах, можно не подключать*}
{*https://cdnjs.com/libraries/noUiSlider*}
{'/assets/vendor/nouislider.min.css'|cssToHead}
{'/assets/vendor/nouislider.min.js'|jsToBottom}

{*скачиваем effect-ui (моя библиотека с vue компонентами, в первую очередь нужна для пагинации), кладём куда хотим, подключаем**}
{*качаем отсюда https://github.com/kanknank/effect-ui/tree/main/dist*}
{*и подключаем основной скрипт магазина*}
{if $dev}
    {'/assets/vendor/effect-ui.js'|jsToBottom}
    {'/assets/components/effectshop/shop.js'|jsToBottom}
{else}
    {'/assets/vendor/effect-ui.min.js'|jsToBottom}
    {'/assets/components/effectshop/shop.min.js'|jsToBottom}
{/if}

{*это чтоб js знал id и контекст ресурса*}
{set $res = [
    'id' => ''|resource:'id',
    'ctx' => ''|resource:'context_key',
]}
<script>window.resource = {$res|toJSON}</script>
```

Нужно создать страницы с такими alias: cart, search, favorites, auth, cabinet. Не все обязательны, думаю по названиям понятно, какие для чего нужны.

В системных настройках в разделе effectshop задать id шаблонов товара и каталога, в основном разделе задать mail_to (почта на которую отправлять письма).
