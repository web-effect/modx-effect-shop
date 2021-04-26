<?php
$xpdo_meta_map['shop_order'] = array(
	
	'package' => 'effectshop',
	'version' => null,
	'table' => 'shop_orders',
	'extends' => 'xPDOSimpleObject',
	
	'tableMeta' => [
    	'engine' => 'InnoDB',
	],

	'fields' => [
		'id' => null,
		'status' => null,
		'contacts' => null,
		'items' => null,
		'options' => null,
		'history' => null,
		'date' => null,
		'delivery' => null,
		'payment' => null,
		'price' => 0,
		'delivery_price' => 0,
		'discount' => 0,
		'total_price' => 0,
		'userid' => 0,
	],
	
	'fieldMeta' => [
		
		'id' => [
			'dbtype' => 'int', 'precision' => '11', 'phptype' => 'integer',
			'null' => false, 'index' => 'pk', 'generated' => 'native',
		],
		
		'contacts' => [
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		],
		'options' => [
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		],
		'items' => [
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		],
		'history' => [
			'null' => true,
			'dbtype' => 'json', 'phptype' => 'json', 
		],

		'date' => [
			'null' => true,
            'dbtype' => 'datetime', 'phptype' => 'datetime',
            'default' => 'CURRENT_TIMESTAMP'
		],
		
		'status' => [
			'dbtype' => 'varchar', 'precision' => '100', 'phptype' => 'string', 
			'null' => true,
		],
		'delivery' => [
			'dbtype' => 'varchar', 'precision' => '100', 'phptype' => 'string', 
			'null' => true,
		],
		'payment' => [
			'dbtype' => 'varchar', 'precision' => '100', 'phptype' => 'string', 
			'null' => true,
		],
		
		'price' => [
			'dbtype' => 'double', 'phptype' => 'float',
			'null' => false, 'default' => 0,
		],
		'delivery_price' => [
			'dbtype' => 'double', 'phptype' => 'float',
			'null' => false, 'default' => 0,
		],
		'discount' => [
			'dbtype' => 'double', 'phptype' => 'float',
			'null' => false, 'default' => 0,
		],
		'total_price' => [
			'dbtype' => 'double', 'phptype' => 'float',
			'null' => false, 'default' => 0,
		],
		
		'userid' => [
			'dbtype' => 'int', 'precision' => '11', 'phptype' => 'integer',
			'null' => false, 'default' => 0,
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
	],

);