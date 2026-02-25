<?php

return [
    'api_id' => env('FANZA_API_ID', ''),
    'affiliate_id' => env('FANZA_AFFILIATE_ID', ''),
    'base_url' => 'https://api.dmm.com/affiliate/v3/',
    'site' => 'FANZA',

    'services' => [
        'digital' => [
            'name' => '動画',
            'floors' => [
                'videoa' => '動画',
                'videoc' => 'VR動画',
                'nikkatsu' => '成人映画',
            ],
        ],
        'mono' => [
            'name' => 'DVD通販',
            'floors' => [
                'dvd' => 'DVD',
                'blu_ray' => 'ブルーレイ',
            ],
        ],
        'ebook' => [
            'name' => '電子書籍',
            'floors' => [
                'comic' => 'コミック',
                'novel' => 'ノベル',
            ],
        ],
    ],

    'categories' => [
        'douga' => [
            'label' => '動画',
            'service' => 'digital',
            'floor' => 'videoa',
            'icon' => 'play-circle',
            'description' => '最新の人気動画作品をチェック',
        ],
        'vr' => [
            'label' => 'VR',
            'service' => 'digital',
            'floor' => 'videoc',
            'icon' => 'vr-cardboard',
            'description' => '没入感あふれるVR動画作品',
        ],
        'dvd' => [
            'label' => 'DVD',
            'service' => 'mono',
            'floor' => 'dvd',
            'icon' => 'disc',
            'description' => 'DVD・ブルーレイの通販',
        ],
        'comic' => [
            'label' => 'コミック',
            'service' => 'ebook',
            'floor' => 'comic',
            'icon' => 'book',
            'description' => '人気コミックを電子書籍で',
        ],
    ],

    'genres' => [
        'joshikosei' => ['label' => '女子校生', 'keyword' => '女子校生'],
        'jukujo' => ['label' => '熟女', 'keyword' => '熟女'],
        'kyonyuu' => ['label' => '巨乳', 'keyword' => '巨乳'],
        'hitozuma' => ['label' => '人妻', 'keyword' => '人妻'],
        'slender' => ['label' => 'スレンダー', 'keyword' => 'スレンダー'],
        'ol' => ['label' => 'OL', 'keyword' => 'OL'],
        'nurse' => ['label' => 'ナース', 'keyword' => 'ナース'],
        'cosplay' => ['label' => 'コスプレ', 'keyword' => 'コスプレ'],
        'lotion' => ['label' => 'ローション', 'keyword' => 'ローション'],
        'pantyhose' => ['label' => 'パンスト', 'keyword' => 'パンスト'],
        'maid' => ['label' => 'メイド', 'keyword' => 'メイド'],
        'sister' => ['label' => '姉', 'keyword' => '姉'],
        'chikan' => ['label' => '痴漢', 'keyword' => '痴漢'],
        'ntr' => ['label' => '寝取り・寝取られ', 'keyword' => '寝取り 寝取られ'],
        'creampie' => ['label' => '中出し', 'keyword' => '中出し'],
        'anal' => ['label' => 'アナル', 'keyword' => 'アナル'],
        'squirting' => ['label' => '潮吹き', 'keyword' => '潮吹き'],
        'mosaic' => ['label' => 'モザイク破壊', 'keyword' => 'モザイク破壊'],
        'amateur' => ['label' => '素人', 'keyword' => '素人'],
        'massage' => ['label' => 'マッサージ', 'keyword' => 'マッサージ'],
        'lesbian' => ['label' => 'レズ', 'keyword' => 'レズ'],
        'threesome' => ['label' => '3P・4P', 'keyword' => '3P 4P'],
        'uniform' => ['label' => '制服', 'keyword' => '制服'],
        'swimsuit' => ['label' => '水着', 'keyword' => '水着'],
    ],

    'default_hits' => 20,
    'cache_ttl' => 3600,
];
