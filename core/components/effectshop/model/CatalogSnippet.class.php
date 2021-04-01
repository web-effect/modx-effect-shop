<?php
namespace Shop;

/**
 * Для работы сниппета shop
 * Название класса не очень удачное, но другого не придумал
 */

class CatalogSnippet extends Catalog
{

    public static function request($action)
    {
        switch ($action) {
            case 'renderCatalog':
                $filter = $_POST['filter'] ?? [];
                return self::getProductsSnippet($filter, true);
        }
    }


    /**
     * $main: режим с пагинацией и аякс-запросами, для каталога.
     */
    public static function getProductsSnippet(array $props = [], $main = false)
    {
        $out = [];

        if ($main) {
            $props = array_merge($_SESSION['shop_products_snippet'] ?? [], $props);
        }
        
        $filterProps = $props; // для кэша, чтоб в массиве не было tpl и т. д.
        foreach (['id', 'tpl', 'main'] as $p) {
            if (isset($filterProps[$p])) unset($filterProps[$p]);
        }

        $uri = strtok(strtok($filterProps["uri"], '?'), '/');
        $out['pageType'] = 'default';
        
        switch ($uri) {
            case 'favorites':
                $out['pageType'] = $uri;
                $ids = json_decode($_COOKIE['shop_favorites'] ?? []);
                $ids = implode(',', $ids);
                $filterProps['where'][] = "modResource.id IN ($ids)";
                break;
            case 'search':
                $out['pageType'] = $uri;
                break;
        }

        if ($out['pageType'] == 'default') {
            $filterProps['section'] = $filterProps['section'] ?? $props['id'];
        }
        
        $catalog = self::getMany($filterProps, $main);
        $out = array_merge($out, $catalog);

        if ($main) {
            $out['html'] = "<div hidden class='shop-catalog-data'
                data-page-type='{$out['pageType']}'
                data-total='{$out['total']}'
                data-limit='{$out['limit']}'
            ></div>";
            $out['html'] .= Shop::parseTpl($props['tpl'], array_merge($out, $props));
        }

        return $out;
    }


    /**
     * Значения фильтров
     */
    public static function getFilters($section = 0)
    {
        $cache = Shop::fromCache('filters_', $section, 'resource');
		if ($cache) return $cache;

		$time_start = microtime(true);
		global $modx;
		$cfg = Params::cfg();
		$tvsMap = self::tvsMap();

		$out = [];
		$out['list'] = array_keys($tvsMap);

		$sections = self::getChildIds($section);


        $qrWhere = [
            "tv.type" => "number",
			'tv.name:NOT IN' => $cfg['filter_exclude']
        ];

		$qr = $modx->newQuery('modTemplateVarResource');
		$qr->select([
			'tv.name',
			'tv.caption',
			'MIN(modTemplateVarResource.value * 1) as min',
			'MAX(modTemplateVarResource.value * 1) as max',
		]);
		
		$qr->groupBy('tv.id');
        $qr->innerJoin('modTemplateVar', "tv", "modTemplateVarResource.tmplvarid = tv.id");
        
        if ($cfg['filter_collections']) {
            $qr->innerJoin(
                'CollectionSelection',
                'coll',
                'coll.resource = modTemplateVarResource.contentid AND coll.collection IN (' . implode(',', $sections) .')'
            );
        }  else {
            $qr->innerJoin('modResource', 'res', 'res.id = modTemplateVarResource.contentid');
            $qrWhere['res.parent:IN'] = $sections;
        }

        $qr->where($qrWhere);

		$qr->prepare();
		$qr->stmt->execute();
		$rows_range = $qr->stmt->fetchAll(\PDO::FETCH_ASSOC);
        

		foreach ($rows_range as $row) {
            $min = (int)$row['min'];
            $max = (int)$row['max'];
            
            if ($min !== $max) {
                $step = 10 ** (strlen($max - $min) - 2);

                $out['filters'][$row['name']] = [
                    'type' => 'range',
                    'label' => $row['caption'],
                    'values' => [$min, $max],
                    'step' => $step < 1 ? 1 : $step,
                ];
            }
		}


        /* Значения TV в текущем разделе */
        $qWhere = [
            'tv.type:IN' => ['listbox', 'listbox-multiple'],
			'tv.name:NOT IN' => $cfg['filter_exclude']
        ];

		$q = $modx->newQuery('modTemplateVarResource');
        $q->select([
			'DISTINCT(`modTemplateVarResource`.`value`) as `value`',
			'tv.name',
			'tv.caption',
			'tv.elements',
        ]);

		$q->innerJoin('modTemplateVar', "tv", "modTemplateVarResource.tmplvarid = tv.id");
        if ($cfg['filter_collections']) {
            $q->innerJoin(
                'CollectionSelection',
                'coll',
                'coll.resource = modTemplateVarResource.contentid AND coll.collection IN (' . implode(',', $sections) .')'
            );
        }  else {
            $q->innerJoin('modResource', 'res', 'res.id = modTemplateVarResource.contentid');
            $qWhere['res.parent:IN'] = $sections;
        }
        
        $q->where($qWhere);

        $q->prepare();
		$q->stmt->execute();
		$rows_tvs = $q->stmt->fetchAll(\PDO::FETCH_ASSOC);
		
		$dicts_values = [];

		foreach ($rows_tvs as $row) {
			$out['filters'][$row['name']] = ['type' => 'dict'];
			$out['filters'][$row['name']] = [
				'type' => 'list',
				'label' => $row['caption'],
			];

			$values = explode('||', $row['value']);

			if (stripos($row['elements'], 'SELECT') !== false ) {
				foreach ($values as $v) {
					if ((int)$v) $dicts_values[] = (int)$v;
				}
			}
		}

		array_unique($dicts_values);


        /* Справочники */
        $dicts_titles = [];
        if (!empty($dicts_values)) {
            $qd = $modx->newQuery('modResource');
            $qd->select(['id', 'pagetitle', 'uri']);
            $qd->where([
                'id:IN' => $dicts_values
            ]);
            $qd->prepare();
            $qd->stmt->execute();
            $dicts_titles = $qd->stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $dicts_uris = array_column($dicts_titles, 'uri', 'id');
		$dicts_titles = array_column($dicts_titles, 'pagetitle', 'id');


		foreach ($rows_tvs as $k => $row) {
			$values = explode('||', $row['value']);
			foreach ($values as $v) {
                $elValues = [];
				$els = explode('||', $row['elements']);
                foreach ($els as $el) {
                    $tmp = explode('==', $el);
                    $elValues[$tmp[1]] = $tmp[0];
                }
				$out['filters'][$row['name']]['values'][] = [
					'label' => $dicts_titles[(int)$v] ?? $elValues[$v] ?? $v,
					'value' => $v,
                    'uri' => $dicts_uris[(int)$v] ?? $l,
				];
			}
            $out['filters'][$row['name']]['expanded'] = false; // для показать ещё, если много значений. Если не задать тут, то св-во не реактивно во Vue
			//удаляем повторяющиеся
			$out['filters'][$row['name']]['values'] = array_map(
				"unserialize",
				array_unique(array_map("serialize", $out['filters'][$row['name']]['values']))
			);
			array_multisort($out['filters'][$row['name']]['values']);
		}


		$out['debug'] = [
			'time' => microtime(true) - $time_start,
			//'sql' => $qr->toSQL(),
		];

		Shop::toCache($out, 'filters_', $section, 'resource');
		return $out;
    }
}