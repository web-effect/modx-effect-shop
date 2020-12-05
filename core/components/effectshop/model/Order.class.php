<?php
namespace Shop;

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
        $this->modx->addPackage('effectshop', MODX_CORE_PATH . 'components/effectshop/model/');
        $this->cfg = Params::cfg();
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
                return $this->saveOrder($_POST['order'], (int)$_POST['id']);
            case 'changeStatus':
                // todo проверка прав
                $status = $_POST['status'] ?? self::ST_CANCELED;
                return $this->changeStatus((int)$_POST['id'], $status, $_POST['comment'] ?? '');

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
        
        //собираем контакты
        foreach ($this->cfg['contact_fields'] as $name => $label) {
            if(!empty($post[$name])) {
                $order['contacts'][$name] = $post[$name];
            }
        }
 
        $save = $this->saveOrder($order);
        if ($save[0] != 1) return [0, ($save[1] ?: 'Ошибка '.__LINE__)];
        $order = $save[1];
        
        // оплата
        if (file_exists(MODX_CORE_PATH . 'components/effectpay/autoload.php')) {
            require MODX_CORE_PATH . 'components/effectpay/autoload.php';
            $payResp = \Pay::payment($order['id'], $order['payment']);
            if ($payResp['method']) {
                $order['options']['pay_link'] = $payResp['pay_link'];
                $order['options']['pay_key'] = $payResp['pay_key'];
                if ($payResp['error']) {
                    $order['options']['pay_error'] = $payResp['error'];
                }
                $order['status'] = $payResp['error'] ? self::ST_PAY_ERROR : self::ST_PAY_WAIT;
                $this->saveOrder($order, $order['id']);
            }
        }

        $mail = Mail::send([
            'to' => $this->cfg['mail_to'],
            'subject' => "На сайте SITENAME сделан новый заказ",
            'pls' => [
                'mode' => 'new',
                'order' => $order,
            ]
        ]);
        
        $mail2 = Mail::send([
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
        if(!empty($order['history'])) {
            $array['history'] = $order['history'];
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
        $orders = $this->getAll([
            'id' => $id,
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
                'userid' => $this->modx->user->id,
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
        $params = Params::getSettings();
        $objFields = array_keys($this->modx->getFields('shop_order'));
        $rows = [];
        $where = [];

        $q = $this->modx->newQuery('shop_order');
        $q->select($objFields);
        
        foreach ($c as $key => $value) {
            if (in_array($key, $objFields)) {
                $where[] = "`$key` = '$value'";
            }
            if (stripos($key, 'contacts__') !== false) {
                $arr = explode('__', $key);
                $search = mb_strtolower($value);
                $where[] = ["LOWER(JSON_EXTRACT(contacts, '$.{$arr[1]}')) LIKE '%{$search}%'"];
            }
        }
        if (!empty($c['dates'])) {
            $dateFrom = date('Y-m-d 00:00:00', $c['dates'][0]);
            $dateTo = date('Y-m-d 23:59:59', $c['dates'][1]);
            $where[] = "`date` > '$dateFrom'";
            $where[] = "`date` < '$dateTo'";
        }
        $q->where($where);
        
        if (stripos($c['sortField'], 'contacts__') !== false) {
			$arr = explode('__', $c['sortField']);
			$c['sortField'] = "JSON_EXTRACT(contacts, '$.{$arr[1]}')";
		}
        if (!empty($c['sortField'])) {
            $q->sortby($c['sortField'], $c['sortDir'] ?? 'ASC');
        } else {
            $q->sortby('id', 'DESC');
        }
        
        $page = 1;
        if (!empty($c['page'])) {
            $page = $c['page'];
            $q->limit($limit, ($page - 1) * $limit);
        }

        $q->prepare();
        $q->stmt->execute();
        $q_result = $q->stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($q_result as $key => $res) {
            $fields = $res;
            
            foreach($fields as $key => $value) {
                if (in_array($key, ['history', 'items', 'contacts', 'options'])) {
                    $fields[$key] = json_decode($value, true) ?: [];
                }
            }

            $fields['date'] = date('Y-m-d H:i', strtotime($fields['date']));
            $fields['contact_fields'] = $cfg['contact_fields'];

            $stNames = array_column($params['statuses'], 'label', 'key');
            $stColors = array_column($params['statuses'], 'color', 'key');
            $fields['status_name'] = $stNames[$fields['status']] ?: $fields['status'];
            $fields['status_color'] = $stColors[$fields['status']] ?: '#000000';

            foreach($fields['history'] as &$row) {
                $row['status_name'] = $stNames[$row['status']] ?: $row['status'];
            }
            unset($row);
            
            $deliveryNames = array_column($params['delivery'], 'label', 'key');
            $paymentNames = array_column($params['payment'], 'label', 'key');
            $fields['delivery_name'] = $deliveryNames[$fields['delivery']] ?? $fields['delivery'] ;
            $fields['payment_name'] = $paymentNames[$fields['payment']] ?? $fields['payment'] ;
                
            $rows[] = $fields;
        }
        
        $total = count($rows);
        if ($total == $limit || $page != 1) {
            $total = $this->modx->getCount('shop_order', $where);
        }
        $out = [
            'rows' => $rows ?? [],
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'debug' => [
                'c' => $c,
                'where' => $where,
            ],
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

        $order['history'][] = [
            'status' => $status,
			'date' => strftime('%Y-%m-%d %H:%M:%S'),
			'comment' => $comment,
        ];

        $save = $this->saveOrder($order, $id);
        return [
            0 => $save[0],
            1 => $save[1] ?? 'Ошибка '.__LINE__,
        ];
    }

}