<?php
$xpdo_meta_map['shop_config']= array (
	
	'package' => 'shop',
	'version' => null,
	'table' => 'shop_config',
	'extends' => 'xPDOSimpleObject',
	
	'tableMeta' => [
    	'engine' => 'InnoDB',
	],

	'fields' => array (
		'setting' => null,
		'value' => null,
	),
	
	'fieldMeta' => array (
		'setting' => array (
			'dbtype' => 'varchar',
			'precision' => '100',
			'phptype' => 'string',
			'null' => true,
		),
		'value' => array (
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		),
	),
	
);