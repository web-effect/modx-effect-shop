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
        $this->modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');
        $this->cfg = Params::cfg();
        
        if (!$order) {
            $this->editOrderMode = false;
            $this->cart = $this->get();
        } else {
            $this->editOrderMode = true;
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

        $output[0] = 1;
        $output['cart'] = $this->processCart();

        if (!$this->editOrderMode && $action != 'clean') {
            $this->setToDB();
        }

        return $output;
    }



    /**
     * Создание корзины в БД
     */
    private function new() {
        $key = uniqid();
        setcookie("shop_cart_key", $key, time()+3600*24*365*10, '/');
        $obj = $this->modx->newObject('shop_cart');
        if ($this->modx->user && $this->modx->user->id) {
            $obj->set('userid', $this->modx->user->id);
        }
        $obj->set('key', $key);
        $obj->save();
    }


    /**
     * Получение корзины из БД
     */
    public function get() {
        $key = $_COOKIE['shop_cart_key'] ?: '';
        $cart = false;

        if (!empty($key)) {
            $q = $this->modx->newQuery('shop_cart');
            $q->select([
                'cart', 'id'
            ]);
            $q->where([
                'key' => $key,
            ]);
            $q->prepare();
            $q->stmt->execute();
            $result = $q->stmt->fetch(\PDO::FETCH_ASSOC);
            if (empty($result['id'])) {
                $this->new();
                $cart = [];
            } else {
                $cart = json_decode($result['cart'] ?: [], true);
            }
        } else {
            $this->new();
            $cart = [];
        }
        if (empty($cart)) {
            $cart = [];
        }
        if (empty($cart['items'])) $cart['items'] = [];

        return $cart;
    }


    /**
     * Запись данных в корзину
     */
    private function setToDB() {
        $key = $_COOKIE['shop_cart_key'] ?: '';
        $obj = $this->modx->getObject('shop_cart', [
            'key' => $key
        ]);
        if ($obj) {
            $obj->set('cart', $this->cart ?: []);
            $obj->save();
        } else {
            $this->modx->log(1, "Ошибка в корзине key {$key}" . __LINE__);
			// Если вдруг ошибка, чистим куки
            setcookie("shop_cart_key", "", time()-3600, '/');
        }
    }


	/**
	 * Очистить корзину
	 */
    public function clean()
    {
        $key = $_COOKIE['shop_cart_key'] ?: '';
        if (!empty($key)) {
            $obj = $this->modx->getObject('shop_cart', [
                'key' => $key
            ]);
            if ($obj) {
                $obj->remove();
                setcookie("shop_cart_key", "", time()-3600, '/');
                unset($_COOKIE['shop_cart_key']);
            }
        }
		$this->cart['items'] = [];
        $this->new();
    }


	/**
	 * Добавить товар
	 */
	private function add(int $id, int $qty = 1, array $params = [])
	{
		$product = Catalog::getOne($id);
		if (empty($product)) return false;

		$product['initial_price'] = $this->cleanPrice($product['price']);
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
		$product['variation'] = $params['variation'] ?? '';

		$intersect = $this->checkIntersect($product);
		if ($intersect === false) {
			$product = $this->cropImage($product);
			foreach (['addons', 'variations'] as $name) {
				foreach ($product[$name] ?? [] as $a_key => $add) {
					$product[$name][$a_key] = $this->cropImage($add);
				}
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
	 * initial_price — начальная цена товара (с учетом вариации)
	 * discount_price — цена со скидкой
	 * price — цена со скидкой и доп. товарами
	 * no_discount_price — цена без скидки, с доп. товарами
	 * total_price — цена * кол-во
	 */
	public function processCart()
	{
		$order = $this->cart;

		$fromEventBefore = $this->modx->invokeEvent('ShopCartBeforeProcess', [
			'cart' => $order,
		]);
		if (!empty($fromEventBefore[0])) {
			$order = $fromEventBefore[0];
		}

		$order['price'] = 0;
		$order['qty'] = 0;
		
		foreach($order['items'] as $k => &$item) {

			// если есть вариация, цену из управления заказами (пока) поменять нельзя
			if ($item['variation'] != '') {
				$varPrice = $this->cleanPrice($item['variations'][(float)$item['variation']]['price'] ?: 0);
				if ($varPrice) {
					$item['initial_price'] = $varPrice;
				}
			}

			$item['discount_percent'] = $item['discount'] ?? $order['discount'] ?? 0;
			$item['discount_val'] = $item['initial_price'] * ($item['discount_percent'] / 100);
			$item['discount_price'] = round(($item['initial_price'] - $item['discount_val']));
			$item['price'] = $item['discount_price'];
			$item['no_discount_price'] = $item['initial_price'];

			if (!empty($item['addons'])) {
				foreach ($item['addons'] as &$add) {
					$add['initial_price'] = $this->cleanPrice($add['initial_price'] ?? (float)$add['price'] ?? 0);
					$add['discount_percent'] = $add['discount'] ?? $item['discount'] ?? $order['discount'] ?? 0;
					$add['discount_val'] = $add['initial_price'] * ($add['discount_percent'] / 100);
					$add['price'] = round($add['initial_price'] - $add['discount_val']);
					$add['qty'] = (float)$add['qty'] ?: 0;
					$add['total_price'] = $add['price'] * $add['qty'];
					
					$item['price'] += $add['total_price'];
					$item['no_discount_price'] += $add['initial_price'] * $add['qty'];
				}
				unset($add);
			}

			$item['total_price'] = $item['price'] * $item['qty'];

			$order['qty'] += $item['qty'];
			$order['price'] += $item['total_price'];
		}
		unset($item);
		
		$order['delivery_price'] = $this->cleanPrice($order['delivery_price'] ?? 0);
		$order['total_price'] = $order['price'] + $order['delivery_price'];

		$fromEventAfter = $this->modx->invokeEvent('ShopCartAfterProcess', [
			'cart' => $order,
		]);
		if (!empty($fromEventAfter[0])) {
			$order = $fromEventAfter[0];
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

		foreach ($product['addons'] ?? [] as $k => &$addon) {
			ksort($product['addons'][$k]);
		}

		for ($i=0; $i < count($this->cart['items']); $i++) {
			$addons = $this->cart['items'][$i]['addons'] ?? [];
			foreach ($addons as $k => &$addon) {
				if (!empty($addon['thumb'])) unset($addon['thumb']);
				ksort($addons[$k]);
			}
			if (
				$this->cart['items'][$i]['id'] == $product['id']
				&& $this->cart['items'][$i]['price'] == $product['price']
				&& $this->cart['items'][$i]['variation'] == $product['variation']
				&& json_encode($this->cart['items'][$i]['options'] ?? []) == json_encode($product['options'] ?? [])
				&& json_encode($addons) == json_encode($product['addons'] ?? [])
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