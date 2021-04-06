<?php
$xpdo_meta_map['shop_cart'] = array(
	
	'package' => 'effectshop',
	'version' => null,
	'table' => 'shop_carts',
	'extends' => 'xPDOSimpleObject',
	
	'tableMeta' => [
    	'engine' => 'InnoDB',
	],

	'fields' => [
		'id' => null,
		'key' => null,
		'userid' => 0,
		'date' => null,
		'cart' => null,
	],
	
	'fieldMeta' => [
		'id' => [
			'dbtype' => 'int', 'precision' => '11', 'phptype' => 'integer',
			'null' => false, 'index' => 'pk', 'generated' => 'native',
		],
		'key' => [
			'dbtype' => 'varchar', 'precision' => '100', 'phptype' => 'string', 
			'null' => false, 'index' => 'key'
		],
		'userid' => [
			'dbtype' => 'int', 'precision' => '11', 'phptype' => 'integer',
			'null' => false, 'default' => 0, 'index' => 'userid'
		],
		'date' => [
			'null' => true,
			'dbtype' => 'datetime', 'phptype' => 'datetime',
			'default' => 'CURRENT_TIMESTAMP'
		],
		'cart' => [
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		],
	],
  
	'indexes' => [
		'PRIMARY' => [
			'alias' => 'PRIMARY',
			'primary' => true,
			'unique' => true,
			'columns' => [
				'id' => [
					'collation' => 'A',
					'null' => false,
				],
			],
		],
		'key' => [
			'alias' => 'key', 'primary' => false, 'unique' => false, 'type' => 'BTREE',
			'columns' => [
				'key' => [ 'length' => '', 'collation' => 'A', 'null' => false ],
			],
		],
		'userid' => [
			'alias' => 'userid', 'primary' => false, 'unique' => false, 'type' => 'BTREE',
			'columns' => [
				'userid' => [ 'collation' => 'A', 'null' => false ],
			],
		],
	],

);