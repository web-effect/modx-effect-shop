# Каталог

TODO
- описание параметров к сниппету shop
- Описание инпутов в карточке товара

У товара должны быть tv price, image. Опционально: addons (таблица с доп. товарами), price_old.


## Опции товара
В форме товара `name='opt-$name' value='$value'`. Обязательны для выбора. Не имеют цены. Если не выбраны, к форме применяется класс (написать какой). 


## Доп. товары
Берутся из migx-таблицы addons. Можно добавить в корзину несколько (см. Ланч Киров).


## Карточка товара (пример)
TODO добавить опции и подтовары, добавление в избранное
```html
<form class="shop-item">
	<input type="hidden" name="id" value="<$$i.id$>">

	<$if $i.discount$>
		<div class="">−<$$i.discount$>%</div>
	<$/if$>

	<!-- тут обновляется цена в зависимости от количества -->
	<span data-price="<$$i.price$>"><$i.price$></span>

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

## Получаем новинки (по TV)
prod_label — имя tv, в значенях которого м. б. action||new||hit
```html
{set $catalog = '!shop'|snippet:[
	'action' => 'getProducts',
	'filter_prod_label' => ['new'],
]}
{foreach $catalog.rows as $key => $i}
	{include 'prod-item'}
{/foreach}
```

## Получаем товары из подборки
```html
<$set $catalog = '!shop'|snippet:[
	'action' => 'getProducts',
	'section' => 11,
	'selections' => true,
	'limit' => 99,
]$>
<$*$catalog|print*$>
```

## Раздел каталога
```html
<$set:ignore $tpl$>
	<div class="grid shop-catalog-items"> 
		<$foreach $rows as $key => $i$>
			<$include 'shop-item'$>
		<$/foreach$>
		<$if !$total$>
			<div class="grid-item is-full">
				<$$pageType == 'favorites' ? 'В избранном нет товаров' : 'Ничего не найдено'$>
			</div>
		<$/if$>
	</div>
<$/set$>

<$set $catalog = '!shop'|snippet:[
	'action' => 'getProducts',
	'mode' => 'main',
	'tpl' => $tpl,
	'limit' => 12,
]$>

<$$catalog.html$>
<div class="shop-catalog-app">
	<div v-if="pages > 1" class="pagination-wrapper">
		<button v-if="pages > page" @click="showMore()" class="button"><span>Показать ещё</span></button>
		<div v-else></div>
		<ef-pagination
			:total="total"
			:limit="limit"
			:page.sync="page"
			@change="pagination($event)"
		></ef-pagination>
	</div>
</div>
```

## Фильтр
В фильтре автоматически выводятся tv number, listbox, listbox-multiple
```html
<$set $filters =  '!shop'|snippet:[
	'action' => 'getFilters'
]$>
<div data-shop-filters='<$$filters|toJSON$>'></div>

<div class="shop-catalog-app filter-wrapper">

	<button @click="filterVisible = !filterVisible || false" class="filter-toggle" :class="{ 'is-active': filterVisible}">
	    Фильтр
	    <$"/assets/web/fa/solid/chevron-down.svg"|svg$>
    </button>

	<div class="filter" :class="{ 'is-visible': filterVisible}">
		
		<div class="filter-section is-active">
			<div class="filter-section-title">Сортировать по:</div>
			<div class="filter-section-main">
			
				<div class="field select-wrapper">
					<select @change="sorting()" v-model="sort" class="input">
					    <option value="">умолчанию</option>
						<option value="price">цене. сначала недорогие</option>
						<option value="price-desc">цене. сначала подороже</option>
						<option value="pagetitle">алфавиту (А-Я)</option>
						<option value="pagetitle-desc">алфавиту (Я-A)</option>
					</select>
					<div class="select-icon"></div>
				</div>

			</div>
		</div>
		

		<template v-for="f in filtersList">
		
			<template v-if="filters[f] && filters[f].type == 'range'">
				<div v-if="filters[f].values && filters[f].values[0] != filters[f].values[1]"
					class="filter-section"
					:class="{ 'is-active': filterSection == f }"
				>
					<button @click="filterSection = filterSection != f ?  f : ''" class="filter-section-title">
						(# filters[f].label #)
					</button>
					<div class="filter-section-main">
						<ef-ranger
							@range="filter(f, 'range')"
							:limits="filters[f].values"
							v-model="filterForm[f]"
						></ef-ranger>
					</div>
				</div>
			</template>

			<template v-if="filters[f] && filters[f].type == 'list'">
				<div v-if="filters[f].values"
					class="filter-section" 
					:class="{ 'is-active': filterSection == f }"
				>
					<button @click="filterSection = filterSection != f ?  f : ''" class="filter-section-title">
						(# filters[f].label #)
					</button>
					<div class="filter-section-main">
						<template v-for="i in filters[f].values">
							<ef-checkbox @input="filter(f)" v-model="filterForm[f]" :val="i.value">
								(# i.label #)
							</ef-checkbox>
						</template>
					</div>
				</div>
			</template>
			
		</template>


		<div v-if="isFiltered" class="filter-bottom">
			<button @click="reset()" class="button button--color-txt button--bordered">Сбросить фильтр</button>
		</div>

	</div>
</div>
```