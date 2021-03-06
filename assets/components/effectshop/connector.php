<?php

header('Content-Type: application/json');
define('MODX_API_MODE', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/index.php');
$modx = new modX();
$modx->initialize('web');

if ($modx->user && $modx->user->get('sudo')) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ERROR | E_PARSE);
	//error_reporting(E_ALL);
}

require MODX_CORE_PATH . 'components/effectshop/autoload.php';

$out = [];

if ($_REQUEST['to'] == 'shop') {
	$out = Shop\Shop::request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'user') {
	$out = Shop\User::request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'cart') {
	$Cart = new Shop\Cart();
	$out = $Cart->request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'order') {
	$Order = new Shop\Order();
	$out = $Order->request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'catalogSnippet') {
	$out = Shop\CatalogSnippet::request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'catalog') {
	$out = Shop\Catalog::request($_REQUEST['action']);
}

echo json_encode($out ?: 'no response :(');