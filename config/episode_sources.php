<?php

return [
    'player_url_ttl_minutes' => env('EPISODE_SOURCE_PLAYER_URL_TTL_MINUTES', 180),

    'providers' => [
        'youtube_link' => [
            'label' => 'YouTube (enlace)',
            'stores_as' => 'youtube',
        ],
        'youtube_iframe' => [
            'label' => 'YouTube (iframe)',
            'stores_as' => 'youtube',
        ],
        'vimeo' => [
            'label' => 'Vimeo',
        ],
        'dailymotion' => [
            'label' => 'Dailymotion',
        ],
        'byse' => [
            'label' => 'BYSE',
        ],
        'voe' => [
            'label' => 'VOE',
        ],
        'ok' => [
            'label' => 'OK',
        ],
        'netu' => [
            'label' => 'NETU',
        ],
        'bunny_stream' => [
            'label' => 'Bunny Stream',
        ],
        'pixeldrain_cdn' => [
            'label' => 'Pixeldrain CDN',
        ],
        'backblaze_b2' => [
            'label' => 'Backblaze B2',
        ],
        'cloudflare_hls' => [
            'label' => 'Cloudflare HLS',
        ],
    ],
];
