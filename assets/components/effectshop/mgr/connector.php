<?php

ini_set('display_errors', 1);
header('Content-Type: application/json');

define('MODX_API_MODE', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/index.php');
$modx = new modX();
$modx->initialize('mgr');

require MODX_CORE_PATH . 'components/effectshop/autoload.php';

$out = [];

if ($_REQUEST['to'] == 'shop') {
	$out = Shop\Shop::request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'order') {
	$Order = new Shop\Order();
	$out = $Order->request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'catalog') {
	$out = Shop\Catalog::request($_REQUEST['action']);
}

if ($_REQUEST['to'] == 'params') {
	$out = Shop\Params::request($_REQUEST['action']);
}


echo json_encode($out ?: 'no response :(');