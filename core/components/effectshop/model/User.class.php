<?php
namespace Shop;

class User 
{
	/**
	 * 
	 */
	public static function request($action)
	{
		switch ($action) {
			case 'login':
				return self::login($_POST);
			case 'logout':
				return self::logout();
			case 'update':
				return self::update($_POST);
			case 'register':
				return self::register($_POST);
				
			default:	
		}
	}


	/**
	 * Логин
	 */
	public static function login($props)
	{
		global $modx;

		$data = [
			'username' => $props['username'] ?? '',
			'password' => $props['password'] ?? '',
			'rememberme' => 1,
			'login_context' => $props['ctx'] ?? 'web',
		];    
		$response = $modx->runProcessor('/security/login', $data);
		if ($response->isError()) {
			return [
				0 => 0,
				//'debug' => $props,
				'error' => $response->getMessage(),
			];
		} else {
			return [1];
		}
	}
	

	/**
	 * Выход
	 */
	public static function logout()
	{
		global $modx;

		$response = $modx->runProcessor('/security/logout');
		if ($response->isError()) {
			return $response->getMessage();
		} else {
			return [1];
		}
	}


	/**
	 * Получение полей профиля
	 */
	public static function getMyData($props)
	{
		//$cache = Shop::fromCache('user_', $props);
		//if ($cache) return $cache;

		global $modx;
		$out = [];
		$id = $modx->user->id ?: 0;
		
		if ($id && $modx->user->isAuthenticated($props['ctx'] ?? 'web')) {
			$q = $modx->newQuery('modUserProfile');
			$q->where([
				'internalKey' => $id,
			]);
			$q->leftJoin('modUser', 'user', "user.id = modUserProfile.internalKey");
			$q->select(['modUserProfile.id', 'modUserProfile.email', 'user.username', 'fullname', 'phone', 'city', 'extended']);
			$q->prepare();
			$q->stmt->execute();
			$out = $q->stmt->fetch(\PDO::FETCH_ASSOC);
			if (!empty($out['extended'])) {
				$ext = json_decode($out['extended'], true);
				$out = array_merge($out, $ext);
				unset($out['ext']);
			}
		}

		//Shop::toCache($out, 'user_', $props);
		return $out;
	}
	
	
	/**
	 * Обновление данных пользователя
	 */
	public function update(array $data)
	{
		global $modx;
		$id = 0;
		if ($modx->user && $modx->user->id) {
			$id = $modx->user->id;
			$data['username'] = $data['username'] ?? $modx->user->username;
		}

		$data['id'] = $id;
		$data['email'] = $data['username'];
		$data['extended'] = [];

		if (!empty($data['newpassword'])) {
			$data['passwordgenmethod'] = false;
			$data['newpassword'] = 'passwordgenmethod';
			$data['passwordnotifymethod'] = 's';
		}


		$response = $modx->runProcessor('/security/user/update', $data);
		$resp = $response->getResponse();

		return [
			0 => (int)$resp['success'],
			'errors' => array_column($resp['errors'], 'msg', 'id'),
			'message' => $resp['message']
		];
	}


	/**
	 * Регистрация
	 */
	public function register(array $data)
	{
		global $modx;

		$scriptProcessor = [
            //"active" => 1,
            'username' => trim($data['username']),
            'newpassword' => 'passwordgenmethod',
            'passwordgenmethod' => false,
            'passwordnotifymethod'  => 's',
            //'extended' => $extended,
            'email' => trim($data['username']),
			'specifiedpassword' => trim($data['specifiedpassword']),
			'confirmpassword' => trim($data['confirmpassword']),
            'groups' => [ [
                'usergroup' => 4,
                'role' => 1
            ] ]
        ];

		$response = $modx->runProcessor('security/user/create', $scriptProcessor);
		$resp = $response->getResponse();
		
		return [
			0 => (int)$resp['success'],
			'errors' => array_column($resp['errors'], 'msg', 'id'),
			'message' => $resp['message']
		];
    
	}

}
