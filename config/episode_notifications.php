<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delivery mode
    |--------------------------------------------------------------------------
    |
    | Use "all" to notify every active user who has email alerts enabled.
    | Set it to "test" temporarily when a delivery preview is needed.
    |
    */
    'mode' => env('EPISODE_NOTIFICATION_MODE', 'all'),

    'test_recipient' => env('EPISODE_NOTIFICATION_TEST_RECIPIENT', 'sts19813@gmail.com'),
];
