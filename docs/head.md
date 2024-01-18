В head прописать:
```html
{*тестовый или боевой режим. на тестовом включены sourcemaps, в консоль пишутся логи*}
<$set $dev = 0$>
{*скачиваем vue 2 версии, кладём куда хотите, подключаем*}
<$if $dev$>
    <$'assets/vendor/node_modules/vue/dist/vue.js'|minify$>
<$else$>
    <$'/assets/vendor/node_modules/vue/dist/vue.min.js'|minify$>
<$/if$>
{*скачиваем nouislider, кладём куда хотите, подключаем*}
<$'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.css'|minify$>
<$'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.js'|minify$>
{*скачиваем effect-ui (моя библиотека с vue компонентами, в первую очередь нужна для пагинации), кладём куда хотите, подключаем*}
{*подключаем основной скрипт магазина*}
<$if $dev$>
    <$'/assets/vendor/effect-ui/dist/effect-ui.js'|minify$>
    <$'/assets/components/effectshop/shop.js'|minify$>
<$else$>
    <$'/assets/vendor/effect-ui/dist/effect-ui.min.js'|minify$>
    <$'/assets/components/effectshop/shop.min.js'|minify$>
<$/if$>

<$set $res = [
    'id' => ''|resource:'id',
    'ctx' => ''|resource:'context_key',
]$>
<script>window.resource = <$$res|toJSON$></script>
```

Нужно создать страницы с такими alias: cart, search, favorites, auth, cabinet.

В системных настройках в разделе effectshop задать id шаблонов товара и каталога, в основном разделе задать mail_to.
