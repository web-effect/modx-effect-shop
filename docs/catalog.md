# Каталог


## Получаем новинки (по TV)
prod_label — имя tv, в значенях которого м. б. action||new||hit
```html
{set $catalog = '!shopProducts'|snippet:[
	'filter_prod_label' => ['new']
]}
{foreach $catalog.rows as $key => $i}
	{include 'prod-item'}
{/foreach}
```


## Получаем товары из подборки
```html
{set $catalog = '!shopProducts'|snippet:[
	'filter_prod_label' => ['new']
]}
{foreach $catalog.rows as $key => $i}
	{include 'prod-item'}
{/foreach}
```



## Товар
```html
<form class="shop-item">
	<input type="hidden" name="id" value="{$id}">
	<!-- .shop-item-qty не обязателен, по умолчанию кол-во 1 -->
	<div class="shop-item-qty">
		<button class="shop-item-minus">–</button>
		<input type="text" name="qty" value=1>
		<button class="shop-item-plus">+</button>
	</div>
	<!-- к .shop-item-button добавляются классы .is-added и .is-loading -->
	<button class="shop-item-button">В КОРЗИНУ</button>
	<!-- через css показываем / скрываем ссылку (.shop-item .is-added + a) -->
	<a class="" href="/cart">В корзине</a>
</form>
```




### Фильтр
В фильтре автоматически выводятся tv number, listbox, listbox-multiple