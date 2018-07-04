<?php

return [
    // 路由配置
    'route' => [
        'default' => env('MODULE_DEFAULT', 'module/index'),
        'prefix' => env('MODULE_PREFIX', ''), // TODO 留空时需要屏蔽默认的首页路由
        'middleware' => [],
    ],

    // 复制文件(支持闭包)
    'publishes' => [],

    // 命令配置(支持闭包)
    'commands' => [],

    // 模块配置，可通过module_config方法获取
    'modules' => [
        // TODO 按照group_name/module_name格式配置(覆盖composer.json > extra.laravel-module.config)
        'module' => [
            'index' => [
                //
            ]
        ]
    ],

];
