<?php

return [
    // 路由配置
    'route' => [
        'default' => env('MODULE_DEFAULT', 'module/index'),
        'prefix' => env('MODULE_PREFIX', ''), // TODO 留空时需要屏蔽默认的首页路由
        'middleware' => [],
    ],

    // 路由设置
    'router' => function ($router, $module) {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $route = array_get($module, 'route');
        $controller = array_get($module, 'controller');
        $action = camel_case(implode('_', [$method, array_first(array_get($module, 'subfix')) ? : 'index']));
        $middleware = array_get($module, 'composer.extra.laravel-module.middleware', []);

        // 验证控制器中对应方法是否存在，否则模块路由无效
        if (method_exists($controller, $action)) {
            $router->$method($route, ['uses' => "{$controller}@{$action}", 'middleware' => $middleware]);
        }
    },

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
