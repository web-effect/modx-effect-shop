В head прописать:
```html
<$set $dev = 0$>
<$if $dev$>
    <$'assets/vendor/node_modules/vue/dist/vue.js'|minify$>
<$else$>
    <$'/assets/vendor/node_modules/vue/dist/vue.min.js'|minify$>
<$/if$>
<$'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.css'|minify$>
<$'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.js'|minify$>
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

dev включает режим разработки: в консоль пишутся запросы, включены sourcemaps.

effect-ui (библиотека с vue-компонентами) и nouislider подключать не обязательно.

Vue delimiters изменены на (# #), чтоб не было конфликтов с Fenom.

Нужно создать страницы с alias: cart, search, favorites, auth, cabinet.

В системных настройках в разделе effectshop задать id шаблонов товара и каталога, в основном разделе задать mail_to.
На прототипе «Effect» tv mail_to сделать по аналогии с mail_from, чтоб туда подтягивалось значение с главной.