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
        'rental' => [
            'name' => 'DVDレンタル',
            'floors' => [
                'rental_dvd' => 'レンタルDVD',
            ],
        ],
        'digital_book' => [
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
        'rental' => [
            'label' => 'レンタル',
            'service' => 'rental',
            'floor' => 'rental_dvd',
            'icon' => 'box-arrow-up-right',
            'description' => 'DVDレンタルサービス',
        ],
        'comic' => [
            'label' => 'コミック',
            'service' => 'digital_book',
            'floor' => 'comic',
            'icon' => 'book',
            'description' => '人気コミックを電子書籍で',
        ],
    ],

    'default_hits' => 20,
    'cache_ttl' => 3600,
];
