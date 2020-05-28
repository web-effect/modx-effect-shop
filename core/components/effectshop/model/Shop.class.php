<?php
class Shop
{
    /**
     * 
     */
    public static function request($action)
    {
		$out = [];
		$cfg = Params::cfg();
		if (!$cfg['shk']) {
			$settings = Params::getSettings();
		} else {
			$settings = Shopkeeper::getSettings();
		}
        

        switch ($action) {
            case 'load':
                $Cart = new Cart();
                $Cart->cropImages();
                $out['cart'] = $Cart->processCart();
                $out['user'] = User::getMyData($_POST);
                $out['methods'] = [
                    'delivery' => $settings['delivery'] ?? [],
                    'payment' => $settings['payment'] ?? [],
				];
				break;
            case 'mgrLoad':
                $out['sets'] = $settings;
				break;
            default:        
        }

        $out[0] = 1;
        return $out;
    }


    /**
     * 
     */
	public static function parseTpl(string $chunk, array $pls = [])
    {
        global $modx;
		$chunkContent = $chunk; 

		//если в tpl есть пробелы, то это не имя чанка, а инлайновая tpl-ка
		if (stripos($chunk, ' ') === false) {
			$chunk = $modx->getObject('modChunk', array('name'=>$chunk));
			if (!$chunk) return false;
			$chunk->setCacheable(false);
			$chunkContent = $chunk->getContent();
		}

        $modx->getParser();
        if (!$modx->parser instanceof pdoParser) return false;
        $output = $modx->parser->pdoTools->getChunk('@INLINE '. $chunkContent, $pls);
        return $output ?: false;
	}


	/**
	 * 
	 */
	private static function processCacheProps($props)
	{
		if (gettype($props) == 'array') {
			/* приводим массив параметров к одному виду для кэша */
			if (isset($props['q'])) unset($props['q']);
			if (isset($props['page']) && $props['page'] == 1) unset($props['page']);
			$array = array_diff($props, ['']);
			array_multisort($props);
		}
		return md5(json_encode($props));
	}


	/**
	 * 
	 */
	public static function fromCache(string $name, $props = '', $cache_opt = '')
	{
		global $modx;
		$props = self::processCacheProps($props);

		$cacheOptions = [];
		if ($cache_opt == 'resource') {
			$cacheOptions = [xPDO::OPT_CACHE_KEY => 'resource'];
		}

		$cache = $modx->cacheManager->get($name . $props, $cacheOptions);

		if ($cache) {
			$cache['debug']['cache_key'] = $name . $props;
			return $cache;
		} else {
			return false;
		}
	}


	/**
	 * 
	 */
	public static function toCache($data, string $name, $props = '', $cache_opt = '')
	{
		global $modx;
		$props = self::processCacheProps($props);

		$cacheOptions = [];
		if ($cache_opt == 'resource') {
			$cacheOptions = [xPDO::OPT_CACHE_KEY => 'resource'];
		}

		$cache = $modx->cacheManager->set($name . $props, $data, 0, $cacheOptions);
	}

}
