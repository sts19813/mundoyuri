<?php

return [
    'hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('WATCH_PROGRESS_HOSTS', 'video.mundoyuri.com,mundoyuri.com,www.mundoyuri.com'))
    ))),

    'local_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('WATCH_PROGRESS_LOCAL_HOSTS', '127.0.0.1,localhost'))
    ))),

    'providers' => [
        'backblaze_b2',
        'cloudflare_hls',
    ],

    'minimum_seconds' => 15,
    'keep_per_user' => 10,
];
