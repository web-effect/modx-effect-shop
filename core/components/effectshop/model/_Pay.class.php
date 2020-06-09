<?php
namespace Shop;

class Pay
{
	const ST_PAY_WAIT = 'pay_wait';
	const ST_PAY_DONE = 'pay_done';
	const ST_PAY_ERROR = 'pay_error';


 	/**
	 * Возвращает цену в копейках с учетом скидки
	 */
	private static function calcPrice($price, $discount = 0) 
	{
		$rub = $price - ($price * $discount / 100);
		return round($rub * 100);
	}


	/**
	 * 
	 */
	public static function sberbank($order)
	{
		global $modx;

		$keys = $modx->getOption('shop.sberbank', null, []);
		$keys = explode('||', $keys);
		if (!count($keys)) {
			return [0 => 0,	'error' => 'Не заданы ключи'];
		}

		if (empty($order)) {
			return [0 => 0,	'error' => 'Не передан заказ'];
		}

		$orderBundle = [];
		$discount = $order['discount'] ?? 0;
		$tax =  ["taxType" => 7]; // НДС чека по расчётной ставке 20/120

		if (!empty($order['delivery_price'])) {
			$dlv_price = self::calcPrice($order['delivery_price'], 0);
			$orderBundle['cartItems']['items'][] = [
				"positionId" => 'delivery',
				"name" => 'Доставка',
				"itemPrice" => $dlv_price,
				"quantity" => ["value" => 1, "measure" => "шт."],
				"itemAmount" => $dlv_price,
				"itemCode" => 'delivery',
				"tax" => $tax,
			];
		}
		/*
		Не передаём, ибо там жестко валидируется email
		if (!empty($order['contacts']['email'])) {
			$orderBundle['customerDetails']['email'] = $order['contacts']['email'];
		}*/

		foreach ($order['items'] as $p) {
			$orderBundle['cartItems']['items'][] = [
				"positionId" => $p['id'],
				"name" => $p['pagetitle'],
				"itemPrice" => self::calcPrice($p['price'], $discount),
				"quantity" => ["value" => (int)$p['qty'], "measure"=>"шт."],
				"itemAmount" => self::calcPrice($p['total_price'], $discount),
				"itemCode" => 'p_' . $p['id'],
				"tax" => $tax,
			];
		}
		
  
		$url = !$keys[0] ? 'https://3dsec.sberbank.ru' : 'https://securepayments.sberbank.ru';
		$data = [
			'userName' => !$keys[0] ? $keys[1] : $keys[3],
			'password' => !$keys[0] ? $keys[2] : $keys[4],
			'returnUrl' => $modx->makeUrl(1, '', '', 'full'),
			'orderNumber' => $order['id'],
			'amount' => self::calcPrice($order['total_price'], 0),
			'orderBundle'=>json_encode($orderBundle),
			'taxSystem' => 0, //СНО - общая
			'sessionTimeoutSecs' => 60 * 60 * 24 * 7, //неделя
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>  $url . "/payment/rest/register.do",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data)
		));
		$responseStr = curl_exec($curl);
		curl_close($curl);
		
		if ($responseStr){
			$response = json_decode($responseStr, true);
			if (!empty($response['orderId']) && !empty($response['formUrl'])) {
				return [
					0 => 1,
					'pay_id' => $response['orderId'],
					'pay_link' => $response['formUrl'],
				];
			} else {
				return [0 => 0,	'error' => $responseStr];
			}
		} else {
			return [0 => 0,	'error' => 'не пришёл ответ'];
		}
	}


	/**
	 * 
	 */
	public static function sberbankCallback($input)
	{
		$id = $input['orderNumber'];
		$Order = new Order();
		$order = $Order->getOne($id);
		$pay_id = $order['options']['pay_id'];

		if (
			$order
			&& ($order['status'] == self::ST_PAY_WAIT || $order['status'] == self::ST_PAY_ERROR)
			&& $input['operation'] == "deposited"
			&& $input['mdOrder'] == $pay_id
		) {
			$order['status'] = $input['status'] == 1 ? self::ST_PAY_DONE : self::ST_PAY_ERROR;
			$Order->saveOrder($order, $id);
		}
		
	}


}
