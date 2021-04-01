<?php
require MODX_CORE_PATH . 'components/effectshop/autoload.php';

if (empty($id)) {
	$id = $modx->resource->get('id');
}
$id = $id ?? 0;

switch ($action) {
	case 'getProducts':
		if (!empty($mode) && $mode == 'main') {
			// Раздел каталога
			$scriptProperties['id'] = $id;
			// Подборка или нет
			$scriptProperties['selections'] = $modx->resource->class_key == 'SelectionContainer' ? true : false;
			$scriptProperties['uri'] = $_SERVER["REQUEST_URI"];
			$_SESSION['shop_products_snippet'] = $scriptProperties;
			$response = Shop\CatalogSnippet::getProductsSnippet($_GET, true);
		} else {
			// Простой режим
			$response = Shop\CatalogSnippet::getProductsSnippet($scriptProperties);
		}
		break;

	case 'getFilters':
		$response = Shop\CatalogSnippet::getFilters($id);
		break;
}

return $response;