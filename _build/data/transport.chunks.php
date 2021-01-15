<?php
$sconfig = [
    'shop-order-report-default' => [],
];

$name = 'effectshop';

foreach ($sconfig ?: [] as $chunk => $options) {
    $chunk_file = $config['component']['core'].'elements/chunks/'.$chunk.'.chunk.tpl';
    if (!file_exists($chunk_file)) continue;
    $data['modChunk'][$chunk] = [
        'fields'=>[
            'name' => $chunk,
            'description' => $options['description'] ?? '',
            /*'snippet' => trim(str_replace(['<?php', '?>'], '', file_get_contents($snippet_file))),*/
			'source' => 2,
			'static' => true,
			'static_file' => "components/$name/elements/chunks/$chunk.chunk.tpl",
        ],
        'options'=>$config['data_options']['modChunk'],
        'relations'=>[
            'modCategory'=>[
                'main'=>'Chunks'
            ]
        ]
    ];
}