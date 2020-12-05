<?php

spl_autoload_register(function($class) {
	$path = __DIR__ . "/model/";
	if (!file_exists($path . $class . '.class.php')) {
		$class = str_replace("Shop\\", "", $class);
	}
	include_once $path . "$class.class.php";
});
