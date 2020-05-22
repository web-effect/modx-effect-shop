<?php
require MODX_CORE_PATH . 'components/effectshop/autoload.php';

$id = $modx->resource->get('id');

switch ($action) {
	case 'getProducts':
		if (!empty($mode) && $mode == 'main') {
			// Раздел каталога
			$scriptProperties['id'] = $id;
			$_SESSION['shop_products_snippet'] = $scriptProperties;
			$response = CatalogSnippet::getProductsSnippet($_GET, true);
		} else {
			// Простой режим
			$response = CatalogSnippet::getProductsSnippet($scriptProperties);
		}
		break;

	case 'getFilters':
		$response = CatalogSnippet::getFilters($id);
}

return $response;