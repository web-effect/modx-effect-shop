<?php
/**
 * Создание и получение заказов
 */
class Order
{
	const ST_PAY_WAIT = 'pay_wait';
	const ST_PAY_DONE = 'pay_done';
	const ST_PAY_ERROR = 'pay_error';
	const ST_CANCELED = 'canceled';


	public function __construct()
	{
		global $modx;
		$this->modx = &$modx;
		$this->modx->addPackage('shop', MODX_CORE_PATH . 'components/effectshop/model/');
		$this->cfg = Params::cfg();
		$this->shk = $this->cfg['shk'] ?? false; //использовать ли Shopkeeper для заказов
	}


	/**
	 * 
	 */
	public function request($action)
	{

		$output = [];

		switch ($action) {
			case 'sendForm':
				return $this->sendForm($_POST);
			case 'getAll':
				return $this->getAll($_POST);
			case 'getOne':
				return $this->getOne((int)$_POST['id']);
			case 'getMyOrders':
				return $this->getMyOrders();
			case 'update':
				// todo проверка прав
				return $this->saveOrder(json_decode($_POST['order'], true), (int)$_POST['id']);
			case 'changeStatus':
				// todo проверка прав
				$status = $_POST['status'] ?? self::ST_CANCELED;
				return $this->changeStatus((int)$_POST['id'], $status, $_POST['comment']);

			default:	
		}
	}


	/**
	 * Отправка формы заказа
	 */
	private function sendForm($post) {

		if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
			return [0, "Email {$post['email']} неверный"];
		}

		$order = $_SESSION['shop_cart'];
		$order['contacts'] = [];
		$order['options'] = [];
		$order['delivery'] = $post['delivery'] ?? '';
		$order['payment'] = $post['payment'] ?? '';

		$order['userid'] = 0;
		if ($this->modx->user && $this->modx->user->id) {
			$order['userid'] = $this->modx->user->id;
		}
		

		//собираем контакты, в режиме shk не нужно
		if (!$this->shk) {
			foreach ($this->cfg['contact_fields'] as $name => $label) {
				if(!empty($post[$name])) {
					$order['contacts'][$name] = $post[$name];
				}
			}
		}
		
		
		$save = $this->saveOrder($order);
		if ($save[0] != 1) return [0, ($save[1] ?: 'Ошибка '.__LINE__)];
		$order = $save[1];


		$pay_error = false;
		
		if ($order['payment'] == 'sberbank') {
			$pay = Pay::sberbank($order);
			if (!$pay[0]) {
				$pay_error = $pay['error'];
				$order['pay_error'] = "Ошибка онлайн-оплаты:" . $pay_error;
				//return [0, 'Ошибка онлайн-оплаты: ' . $pay['error']];
			} else {
				$order['options']['pay_link'] = $pay['pay_link'];
				$order['options']['pay_id'] = $pay['pay_id'];
			}

			$order['status'] = $pay_error ? self::ST_PAY_ERROR : self::ST_PAY_WAIT;
			$this->saveOrder($order, $order['id']);		
		}
		
		

		$Mail = new Mail($this->modx);
		$cfgMailTo = $this->cfg['mail_to_tv'];
		$mailTo = $this->getTv($cfgMailTo[0], $cfgMailTo[1]);
		
		$mail = Mail::send([
			'to' => $mailTo,
			'subject' => "На сайте SITENAME сделан новый заказ",
			'pls' => [
				'mode' => 'new',
				'order' => $order,
			]
		]);
		
		$mail2 = $Mail->send([
			'to' => $post['email'],
			'subject' => "Вы сделали заказ на сайте SITENAME",
			'pls' => [
				'mode' => 'user',
				'order' => $order,
			]
		]);

		if($mail[0] == 1)  {
			unset($_SESSION['shop_cart']);
			return [1, $order];
		} else {
			return $mail;
		}

	}
	

	/**
	 * Создание нового заказа или сохранение изменений в существующем, если передан id
	 */
	public function saveOrder($order, $id = false)
	{
		$Cart = new Cart();
		$order = $Cart->processCart($order);
		
		if(empty($order['items']) || empty($order['total_price']) || !is_numeric($order['total_price'])) {
			return [0, "Не передан состав заказа или сумма: ".__LINE__];
		}

		if ($this->shk) {
			$Shopkeeper = new Shopkeeper();
			if ($id) {
				$new_id = $Shopkeeper->updateOrder($id, $order);
			} else {
				$new_id = $Shopkeeper->saveOrder($order);
			}
			if (!$new_id) return [0, 'Ошибка сохр. заказа '.__LINE__];
			$order = $this->getOne($new_id);
			if (!$order) return [0, 'Ошибка сохр. заказа '.__LINE__];
			return [1, $order];
		}

	
		$array = [
			'payment' => $order['payment'] ?: '',
			'delivery' => $order['delivery'] ?: '',
			'contacts' => $order['contacts'],
			'items' => $order['items'],
			'price' => $order['price'],
			'delivery_price' => $order['delivery_price'] ?: 0,
			'discount' => $order['discount'] ?: 0,
			'total_price' => $order['total_price'],
			'userid' => $order['userid'] ?: 0,
		];

		if(!empty($order['options'])) {
			$array['options'] = $order['options'];
		}

		if(!$id) {
			//$array['date'] = strftime('%Y-%m-%d %H:%M:%S');
			$array['status'] = 'new';
			$obj = $this->modx->newObject('shop_order');
			$obj->fromArray($array);
		} else {
			$array['status'] = $order['status'];
			$obj = $this->modx->getObject('shop_order',$id);
			foreach($array as $name => $value) {
				$obj->set($name, $value);
			}
		}
		
		if ($obj->save()) {
			$id = $obj->get('id');
			$order = $this->getOne($id);
			if($order) {
				return [1, $order];
			}
			return [0, 'Ошибка сохранения заказа #1'];
		} 
		return [0, 'Ошибка сохранения заказа #2'];
	
	}
	

	/**
	 * Получить заказ
	 */
	public function getOne($id)
	{
		if ($this->shk) {
			$Shopkeeper = new Shopkeeper();
			return $Shopkeeper->getOrder($id);
		}
		
		$orders = $this->getAll([
			'where' => ['id' => $id],
		]);
		if ($orders && !empty($orders['rows'][0])) {
			return $orders['rows'][0];
		} else {
			return false;
		}
	}


	/**
	 * 
	 */

	public function getMyOrders()
	{
		if ($this->modx->user && $this->modx->user->id) {
			$orders = $this->getAll([
				'where'=>['userid' => $this->modx->user->id],
			]);
			return $orders;
		}

		return [];
	}


	/**
	 * 
	 */
	
	public function getAll($c = [], $limit = 25)
	{
		$cfg = Params::cfg();

		$q = $this->modx->newQuery('shop_order');
		$q->select('*');
		
		if(!empty($c['where'])) {
			$total = $this->modx->getCount('shop_order', $c['where']);
			$q->where($c['where']);
		} else {
			$total = $this->modx->getCount('shop_order');
		}
	
		if(!empty($c['sortField'])) {
			$q->sortby($c['sortField'], $c['sortDir'] ?? 'ASC');
		} else {
			$q->sortby('id', 'DESC');
		}
		
		if ($c['page']) {
			$q->limit($limit, ($c['page']-1) * $limit);
		}

		$q->prepare();
		$q->stmt->execute();
		$q_result = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$Params = new Params($this->modx);
		$settings = $Params->getSettings(['statuses']);
		$info = $Params->getSettings();

		foreach($q_result as $key=>$res) {
			
			$fields = [];
			foreach($res as $k=>$v) {
				$new_k = str_replace('shop_order_', '', $k);
				$fields[$new_k] = $v;
			}
			
			$fields['history'] = json_decode($fields['history'], true) ?: [];
			$fields['items'] = json_decode($fields['items'], true) ?: [];
			$fields['contacts'] = json_decode($fields['contacts'], true) ?: [];
			$fields['options'] = json_decode($fields['options'], true) ?: [];
			
			$fields['date'] = date('Y-m-d H:i', strtotime($fields['date']));
			$fields['contact_fields'] = $cfg['contact_fields'];

			$stNames = array_column($settings['statuses'], 'label', 'key');
			$stColors = array_column($settings['statuses'], 'color', 'key');
			$fields['status_name'] = $stNames[$fields['status']] ?: $fields['status'];
			$fields['status_color'] = $stColors[$fields['status']] ?: '#000000';

			foreach($fields['history'] as &$row) {
				$row['status_name'] = $stNames[$row['status']] ?: $row['status'];
			}
			unset($row);
			
			$deliveryNames = array_column($info['delivery'], 'label', 'key');
			$paymentNames = array_column($info['payment'], 'label', 'key');
			$fields['delivery_name'] = $deliveryNames[$fields['delivery']] ?? $fields['delivery'] ;
			$fields['payment_name'] = $paymentNames[$fields['payment']] ?? $fields['payment'] ;
				

			$out[] = $fields;
		}
		

		$out = [
			'rows' => $out,
			'total' => $total,
			'debug' => $c,
		];

		return $out;
	}
	

	/**
	 * 
	 */
	public function changeStatus(int $id, string $status, string $comment = '')
	{
		$order = $this->getOne($id);
		$order['status'] = $status;
		$save = $this->saveOrder($order, $id);
		return [
			0 => $save[0],
			1 => $save[1] ?? 'Ошибка '.__LINE__,
		];
	}


	/**
	 * 
	 */
	private function getTv($name, $id)
	{	
		$val = false;
		$query1 = $this->modx->newQuery('modTemplateVar', [
		    'name' => $name,
		]);
		$query1->select('id');
		$tv_id = $this->modx->getValue($query1->prepare());	
		if ($tv_id) {
		    $query2 = $this->modx->newQuery('modTemplateVarResource', [
		        'tmplvarid' => $tv_id, 'contentid' => $id
		    ]);
		    $query2->select('value');
		    $val = $this->modx->getValue($query2->prepare());
		}
		return $val;
	}
	
}