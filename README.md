> Компонент в разработке

## Что есть
- Корзина
- Форма заказа
- API для получения товаров + сниппет для их вывода
- Каталог с фильтрами, переключением видов, сортировкой, поиском
- Поиск с подсказками
- Набор полифиллов для старых браузеров
- Режим совместимости с Shopkeeper 3: заказы сохраняются в таблицы шопкипера, в админке используется тот же модуль управления заказами. 
- Авторизация


https://trello.com/b/ARFKdKVg/shop

- [Корзина](docs/cart.md)
- [Каталог](docs/catalog.md)



## Особенности, примечания
- Чтоб не было конфликтов с fenom, во vue.js используются разделители (# #)



## Установка
- Скопировать файлы компонента в /core/components/shop
- Далаем симлинк в public_html: `ln -r -s public ../../../public_html/assets/components/effectshop`
 Или можно просто скопировать содержимое public в /assets/components/effectshop 
- Создание таблиц: открываем install.php, вводим всё это в компонент Console 


## Подключение
```html
<!-- nouislider подключать только если нужен ползунок в фильтрах -->
{'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.css'|cssToHead}
{'/assets/vendor/node_modules/nouislider/distribute/nouislider.min.js'|jsToBottom}

{if $_modx->user.sudo}
	<!-- полная версия vue выводит сообщения для отладки -->
	{'assets/vendor/node_modules/vue/dist/vue.js'|jsToBottom}
	<!-- полная версия shop.js с sourcemaps и логами в консоли  -->
	{'/assets/components/shop/shop.js'|jsToBottom}
{else}
	{'assets/vendor/node_modules/vue/dist/vue.min.js'|jsToBottom}
	{'/assets/components/shop/shop.min.js'|jsToBottom}
{/if}

<!-- TODO: под вопросом, нужно ли -->
{set $res = [
	'id' => ''|resource:'id',
	'ctx' => ''|resource:'context_key',
]}
<script>window.resource = {$res|toJSON}</script>

<!-- Полифиллы. Совремменные браузеры не загрузят скрипт с  nomodule -->
<script nomodule src="/assets/components/shop/polyfills.js"></script>
```
