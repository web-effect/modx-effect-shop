<?php
	// Страничка для управления заказами без админки
	// Не готова
	exit;
	define('MODX_API_MODE', true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/index.php');
	$modx = new modX();
	$modx->initialize('web');
	
	$path = MODX_CORE_PATH.'components/shop/';
	require_once $path."model/User.class.php";
	$User = new User($modx);

	if ($User->checkAccess('manager') !== true) {
		exit;
	}
	
?>

<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Shop Manager</title>
	<link rel="shortcut icon" href="favicon.png" type="image/png">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
</head>
	<body>
		<div id="app"></div>
	</body>
	<script src="../manager.js"></script>
</html>