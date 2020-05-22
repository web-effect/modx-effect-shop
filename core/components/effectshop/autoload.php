<?php
ini_set('display_errors', 1);

$path = (__DIR__ . "/model/");

spl_autoload_register(function($class) {
	/*
	if (!file_exists($path . $class . '.class.php')) {
		$class = str_replace("\\", "/", $class);
	} */
	include_once __DIR__ . "/model/$class.class.php";
});
