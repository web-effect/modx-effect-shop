<?php

$sets = [
    'product_tmpls' => [
        'value' => 7
    ],
    'section_tmpls' => [
        'value' => 6
    ],
    'product_get_fields' => [
        'value' => 'introtext'
    ],
    'thumb' => [
        'value' => 'w=110&h=110'
    ],
    'order_report_tpl' => [
        'value' => 'shop-order-report-default'
    ],

    'filter_exclude' => [
        'value' => 'price_old'
    ],
    'filter_collections' => [
        'value' => 0
    ],

    'contact_fields' => [
        'xtype' => 'textarea',
        'value' => "fullname==Имя, email==Email, phone==Телефон, address==Адрес, comment==Комментарий"
    ],

    'subject_order_admin' => [
        'value' => 'На сайте SITENAME сделан новый заказ'
    ],
    'subject_order_user' => [
        'value' => 'Вы сделали заказ на сайте SITENAME'
    ],
    'subject_order_status' => [
        'value' => 'Изменён статус вашего заказа на сайте SITENAME'
    ],
];

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
    /*
    [
        'fields' => [
            'key' => $config['component']['namespace'].'.contacts',
            'value' => "a==b\nb==c",
            'xtype' => 'textarea',
            'namespace' => $config['component']['namespace'],
            'area' => $config['component']['namespace'].'.main'
        ],
        'options' => $config['data_options']['modSystemSetting']
    ],*/
];

foreach ($sets as $key => $set) {
    $data['modSystemSetting'][] = [
        'fields' => [
            'key' => $config['component']['namespace']. '.'. $key,
            'value' => $set['value'] ?? '',
            'xtype' => $set['xtype'] ?? 'textfield',
            'namespace' => $config['component']['namespace'],
            'area' => $config['component']['namespace'].'.main'
        ],
        'options' => $config['data_options']['modSystemSetting']
    ];
}
