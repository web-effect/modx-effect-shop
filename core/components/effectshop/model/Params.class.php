<?php

/**
 * Получение / сохранение настроек магазина
 */
class Params 
{
	public function __construct(modX &$modx)
	{
		$this->modx = &$modx;
		$this->modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');
		$this->cfg = require(__DIR__.'/../config.php');
	}
	

	/**
	 * 
	 */
	public static function request($action)
	{
		switch ($action) {
			case 'update':
				return self::update($_POST);

			default:	
		}
	}


	/**
	 * 
	 */
	public static function cfg()
	{
		return require(__DIR__.'/../config.php');
	}


	/**
	 * Получение настроек из БД
	 */
	public static function getSettings($settings = [])
	{
		
		global $modx;
		$modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');

		$q = $modx->newQuery('shop_config');
		$q->select(['setting', 'value']);
		if (!empty($settings)) {
			$q->where([
				'setting:IN' => $settings
			]);
		}
		$q->prepare();
		$q->stmt->execute();
		$result = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$out = [];
		foreach ($result as $row){
			$out[$row['setting']] = json_decode($row['value'], true) ?: [];
		}
		return $out;
	}


	/**
	 * Сохранение настроек в БД
	 */
	public static function update(array $data)
	{

		global $modx;
		$modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');

		$obj = $modx->getObject('shop_config',[
			'setting' => $data['key']
		]);

		$value = json_decode($data['value'], true);

		if(!$obj || !is_array($value) || !count($value)) {
			return [0, 'Не передано имя настройки или значение '.__LINE__];
		} 
		
		$obj->set('value', $value);
		
		if($obj->save()) {
			return [1, true];
		} else {
			return [0, 'Ошибка '.__LINE__];
		}
	}



}
