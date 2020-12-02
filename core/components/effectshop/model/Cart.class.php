<?php
namespace Shop;

/**
 * Отвечает за корзину на сайте, расчёт цен, скидок в заказе
 */
class Cart
{

	public function __construct($order = false)
	{
		global $modx;
		$this->modx = &$modx;
		$this->cfg = Params::cfg();

		if (!$order) {
			if (empty($_SESSION['shop_cart']) || empty($_SESSION['shop_cart']['items'])) {
				$_SESSION['shop_cart'] = [
					'items' => [],
					'created' => time()
				];
			}
			$this->cart = &$_SESSION['shop_cart'];
		} else {
			$this->cart = &$order;
		}
	}


	/**
	 * Ajax запросы
	 */
	public function request($action)
	{
		$output = [];
		$params = $_POST['params'] ?? [];

		switch ($action) {
			case 'add':
				$output['product'] = $this->add((int)$_POST['id'], (int)$_POST['qty'], $params);
				break;
			case 'remove':
				$output['product'] = $this->remove((int)$_POST['index']);
				break;
			case 'qty':
				$output['product'] = $this->qty((int)$_POST['index'], (int)$_POST['qty']);
				break;
			case 'addonQty':
				$output['product'] = $this->addonQty((int)$_POST['index'], (int)$_POST['addon'], (float)$_POST['qty']);
				break;
			case 'setValue':
				$this->cart[$_POST['key']] = $_POST['val'];
				break;
			case 'clean':
				$this->clean();
				break;
			default:	
		}

		$output['cart'] = $this->processCart();
		$output[0] = 1;
		return $output;
	}


	/**
	 * Очистить корзину
	 */
	private function clean()
	{
		$_SESSION['shop_cart'] = [];
	}


	/**
	 * Добавить товар
	 */
	private function add(int $id, int $qty = 1, array $params = [])
	{
		$product = Catalog::getOne($id);
		if (empty($product)) return false;

		$product['price'] = $this->cleanPrice($product['price']);
		$product['qty'] = $this->cleanCount($qty) ?: 1;
		$product['url'] = $this->modx->makeUrl($product['id'], '', '', 'full' );
		
		if (!empty($params['opts'])) {
			foreach ($params['opts'] as $name => $opt) {
				$product['options'][$name] = $opt;
			}
		}
		if (!empty($params['addons'])) {
			foreach ($params['addons'] as $key => $val) {
				if (empty($product['addons'][(int)$key])) continue;
				$product['addons'][(int)$key]['qty'] = (int)$val;
			}
		}

		$intersect = $this->checkIntersect($product);
		if ($intersect === false) {
			$product = $this->cropImage($product);
			foreach ($product['addons'] ?? [] as $a_key => $add) {
				$product['addons'][$a_key] = $this->cropImage($add);
			}

			array_push($this->cart['items'], $product);
		} else {
			$this->cart['items'][$intersect]['qty'] += $product['qty'];
		}

		return $product;
	}


	/**
	 * Изменить кол-во
	 */
	private function qty(int $index, $qty)
	{
		$product = [];
		if ($this->cart['items'][$index]) {
			$product = $this->cart['items'][$index];
			$this->cart['items'][$index]['qty'] = $this->cleanCount($qty);
		}
		return $product;
	}


	/**
	 * Изменить кол-во доп. товара
	 * Доп. товар есть всегда с кол-вом 0, добавлять его не нужно
	 */
	private function addonQty(int $index, int $a_index, float $qty)
	{
		$product = [];
		if (!empty($this->cart['items'][$index])) {
			$product = $this->cart['items'][$index];
			if (!empty($this->cart['items'][$index]['addons'][$a_index])) {
				$this->cart['items'][$index]['addons'][$a_index]['qty'] = (float)$qty;
			}
		}
		return $product;
	}
	

	/**
	 * 
	 */
	private function remove(int $index)
	{
		$product = [];
		if ($this->cart['items'][$index]) {
			$product = $this->cart['items'][$index];
			array_splice($this->cart['items'], $index, 1);
		}
		return $product;
	}


	/**
	 * 
	 */
	public function processCart($order = false)
	{
		$order = $order ?: $this->cart;

		$order['price'] = 0;
		$order['qty'] = 0;
		
		foreach($order['items'] as $k => &$item) {
			$item['qty'] = (int)$item['qty'];
			$item['initial_price'] = $item['initial_price'] ?? (float)$item['price'];
			$item['price'] = $item['initial_price'];

			if (!empty($item['addons'])) {
				foreach ($item['addons'] as &$add) {
					$add['qty'] = (float)$add['qty'] ?: 0;
					$addPrice = (float)$add['price'] * $add['qty'];
					$item['price'] += $addPrice;
				}
				unset($add);
			}

			$order['qty'] += $item['qty'];
			$item['total_price'] = round(($item['price'] * $item['qty']), 2);
			$order['price'] += $item['total_price'];
		}
		unset($item);
		
		$order['price'] = round($order['price'], 2);
		$order['discount'] = $order['discount'] ?? 0;
		$order['delivery_price'] = $order['delivery_price'] ?? 0;
		$discount = $order['price'] * (((float)$order['discount']) / 100);
		$order['total_price'] = $order['price'] + ((float)$order['delivery_price']) - $discount;

		$fromEvent = $this->modx->invokeEvent('ShopCartAfterProcess', [
			'cart' => $order,
		]);
		if (!empty($fromEvent[0])) {
			$order = $fromEvent[0];
		}

		return $order;
	}


	/**
	 * Обрезка картинки
	 */
	private function cropImage(array $item)
	{
		if (!empty($item['image']) && empty($item['thumb'])) {
			$thumb = $this->modx->runSnippet('phpthumbon', [
				'input' => $item['image'],
				'options' => $this->cfg['thumb'] ?? 'w=70&h=70',
			]);
			$item['thumb'] = $thumb;
		}
		return $item;
	}

	
	/**
	 * Проверяем, есть ли уже товар в корзине
	 */
	private function checkIntersect($product)
	{
		$output = false;
		for( $i=0; $i < count($this->cart['items']); $i++ ){
			if (
				$this->cart['items'][$i]['id'] == $product['id']
				&& $this->cart['items'][$i]['price'] == $product['price']
				&& json_encode($this->cart['items'][$i]['opts'] ?? []) == json_encode($product['opts'] ?? [])
				&& json_encode($this->cart['items'][$i]['addons'] ?? []) == json_encode($product['addons'] ?? [])
			) {
				$output = $i;
				break;
			}
		}
		return $output;
	}
	

	/**
	 * Проверяет введенное число кол-ва товаров и приводит к нормальному виду
	 */
	private function cleanCount($count)
	{
		$output = str_replace(array(',',' '),array('.',''),$count);
		if(!is_numeric($output) || empty($output)) return 1;
		return abs((int)$output);
	}


	/**
	 * 
	 */
	private function cleanPrice($price = 0)
	{
		$price = str_replace([',', ' '], ['.', ''], (string)$price);
		return round((float)$price, 2);
	}


}