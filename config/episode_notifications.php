<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delivery mode
    |--------------------------------------------------------------------------
    |
    | Keep this in "test" until the email design and delivery have been
    | approved. Change it to "all" only when every active user should receive
    | the episode-available email.
    |
    */
    'mode' => env('EPISODE_NOTIFICATION_MODE', 'test'),

    'test_recipient' => env('EPISODE_NOTIFICATION_TEST_RECIPIENT', 'sts19813@gmail.com'),
];
