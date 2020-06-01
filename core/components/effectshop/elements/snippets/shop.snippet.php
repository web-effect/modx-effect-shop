<?php
require MODX_CORE_PATH . 'components/effectshop/autoload.php';

$id = $id ?? $modx->resource->get('id');

switch ($action) {
	case 'getProducts':
		if (!empty($mode) && $mode == 'main') {
			// Раздел каталога
			$scriptProperties['id'] = $id;
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

	case 'getOneFull':
		$response = Shop\CatalogSnippet::getOneFull($id);
		break;
}

return $response;