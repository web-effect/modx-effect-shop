<?php
/**
 * Отвечает за корзину на сайте, расчёт цен, скидок в заказе
 */
class Cart
{

	public function __construct($order = false)
	{
		global $modx;
		$this->modx = &$modx;

		if (!$order) {
			if (empty($_SESSION['shop_cart']) || empty($_SESSION['shop_cart']['items'])) {
				$_SESSION['shop_cart'] = ['items' => []];
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

		switch ($action) {
			case 'add':
				$opts = !empty($_POST['options']) ? json_decode($_POST['options'], true) : '';
				$output['product'] = $this->add((int)$_POST['id'], (int)$_POST['qty'], $opts);
				break;
			case 'remove':
				$this->remove($_POST['index']);
				break;
			case 'qty':
				$this->qty((int)$_POST['index'], (int)$_POST['qty']);
				break;
			default:	
		}

		$output['cart'] = $this->processCart();
		$output[0] = 1;
		return $output;
	}


	/**
	 * 
	 */
	private function add(int $id, int $qty = 1, array $options = [])
	{
		$product = Catalog::getOne($id);
		if (empty($product)) return false;

		$product['price'] = $this->cleanPrice($product['price']);
	
		$product['name'] = htmlspecialchars($product['pagetitle']);
		$product['qty'] = $this->cleanCount($qty) ?: 1;
		$product['url'] = $this->modx->makeUrl($product['id'], '', '', 'full' );
		
		foreach ($options as $name => $opt) {
			$product['options'][$name] = $opt;
		}

		$intersect = $this->checkIntersect($product);
		if ($intersect === false) {
			array_push($this->cart['items'], $product);
		} else {
			$this->cart['items'][$intersect]['qty'] += $product['qty'];
		}

		return $product;
	}


	/**
	 * 
	 */
	private function qty(int $index, $qty)
	{
		if ($this->cart['items'][$index]) {
			$this->cart['items'][$index]['qty'] = $this->cleanCount($qty);
		}
	}
	

	/**
	 * 
	 */
	private function remove(int $index)
	{
		if ($this->cart['items'][$index]) {
			array_splice($this->cart['items'], $index, 1);
		}
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
			$order['qty'] += $item['qty'];
			$item['total_price'] = round(($item['price'] * $item['qty']), 2);
			$order['price'] += $item['total_price'];
		}
		unset($item);
		
		$order['price'] = round($order['price'], 2);
		$order['discount'] = 0;
		$order['delivery_price'] = 0;
		$discount = $order['price'] * (((float)$order['discount']) / 100);
		$order['total_price'] = $order['price'] + ((float)$order['delivery_price']) - $discount;

		return $order;
	}


	/**
	 * Обрезка картинок в корзине
	 */
	public function cropImages()
	{
		$cfg = Params::cfg();
		foreach ($this->cart['items'] as $k => &$item ) {
			if (!empty($item['image']) && empty($item['thumb'])) {
				$path = stripos($item['image'], 'assets/mgr') === false ? "/assets/mgr/{$item['image']}" : $item['image'];
				$thumb = $this->modx->runSnippet('phpthumbon', [
					'input' => $path,
					'options' => $cfg['catalog']['thumb'] ?? 'w=70&h=70',
				]);
				$item['thumb'] = $thumb;
			}
		}
	}

	
	/**
	 * Проверяем, есть ли уже товар в корзине. Взято из Shopkeeper3
	 */
	private function checkIntersect($product)
	{
		$output = false;
		for( $i=0; $i < count($this->cart['items']); $i++ ){
			if( $this->cart['items'][$i]['id'] == $product['id'] ){
				if( $this->cart['items'][$i]['price'] == $product['price'] ){
					if( serialize($this->cart['items'][$i]['options']) == serialize($product['options']) ) {
						$output = $i;
						break;
					}
				}
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