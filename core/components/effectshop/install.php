<?php
$manager = $modx->getManager();
$modx->addPackage('shop', MODX_CORE_PATH . 'components/effectshop/model/');
$manager->createObjectContainer('shop_order');
$manager->createObjectContainer('shop_config');

$obj = $modx->newObject('shop_config');
$obj->set('setting', 'statuses');
$obj->set('value', [
    [ 'key' => 'new', 'label' => 'Новый' ],
    [ 'key' => 'completed', 'label' => 'Завершен' ]
]);
$obj->save();

$obj = $modx->newObject('shop_config');
$obj->set('setting', 'delivery');
$obj->set('value', [
    [ 'key' => 'pickup', 'label' => 'Самовывоз' ],
]);
$obj->save();

$obj = $modx->newObject('shop_config');
$obj->set('setting', 'payment');
$obj->set('value', [
    [ 'key' => 'receipt', 'label' => 'Оплата при получении' ],
]);
$obj->save();
