<?php

$data['modMenu'] = [
    [
        'fields' => [
            'text' => 'Магазин',
            'action' => 'index',
            'parent' => 'topnav',
            'namespace' => $config['component']['namespace'],
            'menuindex' => 4
        ],
        'options' => $config['data_options']['modMenu']
    ]
];