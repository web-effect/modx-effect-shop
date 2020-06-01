<?php
namespace Shop;

/**
 * Для совместимости заказов с Shopkeeper
 */
class Shk
{

	public function __construct()
	{
		global $modx;
		$this->modx = &$modx;
		if(!defined('SHOPKEEPER_PATH')){
			define('SHOPKEEPER_PATH', MODX_CORE_PATH."components/shopkeeper3/");
		}
		$modx->addPackage( 'shopkeeper3', SHOPKEEPER_PATH . 'model/' );
		$this->cfg = Params::cfg();
	}


	/**
	 * 
	 */
	public static function getSettings()
	{
		global $modx;
		if(!defined('SHOPKEEPER_PATH'))define('SHOPKEEPER_PATH', MODX_CORE_PATH."components/shopkeeper3/");
		$modx->addPackage( 'shopkeeper3', SHOPKEEPER_PATH . 'model/' );

		$cf_q=$modx->newQuery('shk_config');
		$cf_q->select(['setting', 'value']);
		$cf_q->where([
			'setting:IN' => ['payments', 'delivery']
		]);
		$cf_q->prepare();

		$cf_q->stmt->execute();
		$cf_result = $cf_q->stmt->fetchAll(\PDO::FETCH_ASSOC);

		$out = [];
		foreach ($cf_result as $set) {
			$key = str_replace(['payments'], ['payment'], $set['setting']);
			$v = json_decode($set['value'], true);
			foreach ($v as $mthd) {
				$out[$key][$mthd['value'] ?? $mthd['label']] = $mthd['label'];
			}
		}

		return $out;
	}


	/**
	 * 
	 */
	public function saveOrder($input)
	{
		global $modx;
		$cfg = Params::cfg();

		$userId = $modx->user ? $modx->user->id : 0;

		
		//Контактные данные
		$contacts_fields = array();
		$response = $modx->runProcessor('getsettings',
			array( 'settings' => array('contacts_fields') ),
			array( 'processors_path' => $modx->getOption( 'core_path' ) . 'components/shopkeeper3/processors/mgr/' )
		);
		if ($response->isError()) {
			echo $response->getMessage();
		}
		if($result = $response->getResponse()){
			$temp_arr = !empty( $result['object']['contacts_fields'] ) ? $result['object']['contacts_fields'] : array();
			if( !empty( $temp_arr ) ){
				foreach( $temp_arr as $opt ){
					$contacts_fields[$opt['name']] = $opt;
				}
			}
		}
		$contacts = array();
		$allFormFields = $_POST;
		foreach( $allFormFields as $key => $val ){	
			if( in_array( $key, array_keys( $contacts_fields ) ) ){
				$temp_arr = array(
					'name' => $contacts_fields[$key]['name'],
					'value' => $val,
					'label' => $contacts_fields[$key]['label']
				);
				array_push( $contacts, $temp_arr );
				$input['contacts'][$contacts_fields[$key]['name']] = $val;
				$input['contact_fields'][$contacts_fields[$key]['name']] = $contacts_fields[$key]['label'];
			}
		}
		$contacts = json_encode( $contacts );
		

		$deliveryField = $_POST['delivery'] ?? '';
		$paymentField = $_POST['payment'] ?? '';
		

		//Доставка
		$delivery_price = !empty( $shopCart->delivery['price'] ) ? $shopCart->delivery['price'] : 0;
		$delivery_name = !empty( $shopCart->delivery['label'] ) ? $shopCart->delivery['label'] : '';
		if( !$delivery_name ){
			$delivery_name = !empty( $allFormFields[$deliveryField] ) ? $allFormFields[$deliveryField] : '';
		}
		
		$date = strftime('%Y-%m-%d %H:%M:%S');

		//Сохраняем данные заказа
		$order = $modx->newObject('shk_order');
		$insert_data = array(
			'contacts' => $contacts,
			'options' => json_encode($input['options'] ?? []),
			'price' => $input['total_price'],
			'currency' => 'руб.',
			'date' => $date,
			'sentdate' => $date,
			'note' => '',
			'email' => $_POST['email'] ?? '',
			'delivery' => $_POST['delivery'] ?? '',
			'delivery_price' => $delivery_price,
			'payment' => $_POST['payment'] ?? '',
			'tracking_num' => '',
			'phone' => $_POST['phone'] ?? '',
			'status' => $modx->getOption( 'shk3.first_status', null, '1' )
		);
		if( $userId ){
			$insert_data['userid'] = $userId;
		}
		$order->fromArray($insert_data);
		$saved = $order->save();


		if ($saved) {
	
	
			foreach( $input['items'] as $key => $p_data ) {

				$options = [];
				
				$options = [];
				foreach ($p_data['opts'] ?? [] as $name => $opt) {
					$options[$name] = [$opt, 0, 0];
				}
				foreach ($p_data['adds'] ?? [] as $name => $add) {
					if ($add['qty']) {
						$options[$add['id']] = [$add['name'], $add['price'], 0];
					}
				}
				$options = json_encode($options);
			

			
				$fields_data = [];
				$fields_data['url'] = !empty( $p_data['url'] ) ? $p_data['url'] : '';
				foreach ($cfg['product_get_fields'] ?? [] as $i) {
					$fields_data[$i] = $p_data[$i];
				}
				//unset( $fields_data['id'] );
				$fields_data_str = json_encode( $fields_data );
	
				$insert_data = array(
					'p_id' => $p_data['id'],
					'order_id' => $order->id,
					'name' => $p_data['name'],
					'price' => $p_data['initial_price'],
					'count' => $p_data['qty'],
					'class_name' => 'modResource',
					'package_name' => '',
					'data' => $fields_data_str,
					'options' => $options
				);
	
				$purchase = $modx->newObject('shk_purchases');
				$purchase->fromArray( $insert_data );
				$saved2 = $purchase->save();
	
			}
	
		
		}
		
		$modx->invokeEvent('OnSHKChangeStatus', array( 'order_ids' => array( $order->id ), 'status' => $order->status ));

		$input['id'] = $order->get('id');
	
		if ($saved && $saved2 && $input['id']) {
			return $input['id'];
		} else {
			return 0;
		}

	}


	/**
	 * 
	 */
	public function updateOrder($id, $data)
	{
		$order = $this->modx->getObject('shk_order', $id);
		if (!$order) return false;

		foreach($data as $name => $value) {
			if (!in_array($name, ['options', 'status'])) continue;

			if (in_array($name, ['options'])) {
				$param = json_decode($order->get($name) ?? '', true);
				$param = array_merge($param, $value);
				$order->set($name, json_encode($param));
			} else if ($name == 'status') {
				$status = $this->cfg['shk_statuses'][$value] ?? false;
				if ($status) $order->set('status', $status);
			} else {
				$order->set($name, $value);
			}
		}

		$order->save();
		return $order->get('id');
	}


	/**
	 * 
	 */
	public function getOrder($order)
	{
		global $modx;
		//Получает данные заказа
		//Взято с Ситирон

		if(is_numeric($order))$order=(int)$order;
		elseif(!is_array($order))$order=json_decode($order,true);

		$order = $modx->getObject('shk_order',$order,false);
		if (!$order) return false;
		$cart_q = $modx->newQuery('shk_purchases',array('order_id'=>(int)$order->id),false);
		$cart_q->prepare();
		$cart_q->stmt->execute();
		$cart=array();

		while ($purchase = $cart_q->stmt->fetch(\PDO::FETCH_ASSOC)) {
			$_keys=explode('||',str_replace('shk_purchases_','',implode('||',array_keys($purchase))));
			$purchase = array_combine($_keys,array_values($purchase));
			$purchase['id'] = $purchase['p_id'];
			$purchase['qty'] = $purchase['count'];
			
			$opts = json_decode($purchase['options'],true) ?: [];
			$purchase['options'] = $opts;
	
			$pdada = json_decode($purchase['data'],true) ?: [];
			foreach ($pdada as $k => $d) {
				if (!isset($purchase[$k])) $purchase[$k] = $d;
			}

			$purchase['total_price'] = round($purchase['price']*$purchase['qty'],2);
			$cart[] = $purchase;
		}

		$_order = $order->toArray();
		$contacts = json_decode($order->contacts,true);
		$_order['contact_fields'] = array_column($contacts, 'label', 'name');
		$_order['contacts'] = array_column($contacts, 'value', 'name');


		$st_q=$modx->newQuery('shk_config',array('setting'=>"statuses"));
		$st_q->select(array('shk_config.value'));
		$st_q->prepare();
		$st_q->stmt->execute();
		$statuses = json_decode($modx->getValue($st_q->stmt),true);
		$st_index = array_search($_order['status'],array_column($statuses,'id'));
		
		$_order['status_name'] = $_order['status'];
		if ($st_index!==false) $_order['status_name'] = $statuses[$st_index]['label'];

	
		$methods = self::getSettings();
		$_order['payment_name'] = $methods['payment'][$_order['payment']] ?? $_order['payment'];
		
		$_order['options'] = json_decode($order->options, true);
		$_order['items'] = $cart;
		$_order['total_price'] = $_order['price'];

		return $_order;
	}
	
}