<?php

return [
    // 路由配置
    'route' => [
        'default' => env('MODULE_DEFAULT', 'module/index'),
        'prefix' => env('MODULE_PREFIX', ''),
    ],

    'parse' => [
        'app_id' => env('PARSE_APPID', ''),
        'rest_key' => env('PARSE_REST_KEY', ''),
        'master_key' => env('PARSE_MASTER_KEY', ''),
        'server_url' => env('PARSE_SERVER_URL', ''),
        'mount_path' => env('PARSE_MOUNT_PATH', ''),
    ]
];
