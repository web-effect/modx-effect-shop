<?php
$modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');
$data['shop_config'] = [
    [
        'fields' => [
            'setting' => 'statuses',
            'value' => [
                [ 'key' => 'new', 'label' => 'Новый', 'color' => '#ff00ff' ],
                [ 'key' => 'completed', 'label' => 'Завершен', 'color' => '#cccccc' ]
            ],
        ],
        'options' => $config['data_options']['shop_config']
    ],
    [
        'fields' => [
            'setting' => 'delivery',
            'value' => [
                [ 'key' => 'pickup', 'label' => 'Самовывоз' ],
            ],
        ],
        'options' => $config['data_options']['shop_config']
    ],
    [
        'fields' => [
            'setting' => 'payment',
            'value' => [
                [ 'key' => 'receipt', 'label' => 'Оплата при получении' ],
            ],
        ],
        'options' => $config['data_options']['shop_config']
    ]
];


$data['modCategory']=[
    'main'=>[
        'fields'=>[
            'category'=>$config['component']['name']
        ],
        'options'=>$config['data_options']['modCategory']
    ],
    /*
    'defaults'=>[
        'fields'=>[
            'category'=>'defaults'
        ],
        'options'=>$config['data_options']['modCategory.child'],
        'relations'=>[
            'modCategory'=>[
                'main'=>'Children'
            ]
        ]
    ]*/
];
