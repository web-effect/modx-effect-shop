<?php

$data['modSystemSetting'] = [
    [
        'fields' => [
            'key' => 'mail_to',
            'value' => '',
            'xtype' => 'textfield',
            'namespace' => 'core',
        ],
        'options' => $config['data_options']['modSystemSetting']
    ],
    [
        'fields' => [
            'key' => $config['component']['namespace'].'.product.tmpls',
            'value' => '7',
            'xtype' => 'textfield',
            'namespace' => $config['component']['namespace'],
            'area' => $config['component']['namespace'].'.main'
        ],
        'options' => $config['data_options']['modSystemSetting']
    ],
    [
        'fields' => [
            'key' => $config['component']['namespace'].'.product.get_fields',
            'value' => '',
            'xtype' => 'textfield',
            'namespace' => $config['component']['namespace'],
            'area' => $config['component']['namespace'].'.main'
        ],
        'options' => $config['data_options']['modSystemSetting']
    ],
    [
        'fields' => [
            'key' => $config['component']['namespace'].'.contacts',
            'value' => "a==b\nb==c",
            'xtype' => 'textarea',
            'namespace' => $config['component']['namespace'],
            'area' => $config['component']['namespace'].'.main'
        ],
        'options' => $config['data_options']['modSystemSetting']
    ],
];
