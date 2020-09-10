<?php
namespace Shop;

/**
 * Получение / сохранение настроек магазина
 */
class Params 
{

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
		$cache = Shop::fromCache('shop_cfg');
        if ($cache) return $cache;

		//$array = require(__DIR__.'/../config.php');

		$array['mail_to'] = self::getOpt('mail_to', '');

		$array['shk'] = self::getOpt('effectshop.shk', 0);
		$array['thumb'] = self::getOpt('effectshop.thumb', 'w=110&h=110');
		$array['order_report_tpl'] = self::getOpt('effectshop.order_report_tpl', 'shop-order-report');

		$array['product_get_fields'] = self::getOpt('effectshop.product_get_fields', '', 1);
		$array['product_tmpls'] = self::getOpt('effectshop.product_tmpls', '7', 1);
		$array['section_tmpls'] = self::getOpt('effectshop.section_tmpls', '6', 1);

		$array['filter_exclude'] = self::getOpt('effectshop.filter_exclude', '', 1);
		$array['filter_collections'] = self::getOpt('effectshop.filter_collections', 0);
		
		$array['contact_fields'] = self::getOpt('effectshop.contact_fields', [], 2);

		Shop::toCache($array, 'shop_cfg');
		return $array;
	}


	/**
	 * 
	 */
	private static function getOpt($option, $default = '', $array = 0)
	{
		global $modx;
		$opt = $modx->getOption($option, null, $default);
		if ($array) {
			$opt = explode(',', $opt);
			$opt = array_map('trim', $opt);
			if ($array == 2) {
				$tmp = [];
				foreach ($opt as $o) {
					$o = explode('==', $o);
					$tmp[trim($o[0])] = trim($o[1]);
				}
				$opt = $tmp;
			}
		}
		return $opt;
	}


	/**
	 * Получение настроек из БД
	 */
	public static function getSettings($settings = [])
	{
		$cache = Shop::fromCache('shop_settings');
        if ($cache) return $cache;

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
		$result = $q->stmt->fetchAll(\PDO::FETCH_ASSOC);
		
		$out = [];
		foreach ($result as $row){
			$out[$row['setting']] = json_decode($row['value'], true) ?: [];
		}

		Shop::toCache($out, 'shop_settings');
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

		$value = $data['value'];
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
