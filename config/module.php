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
        $middleware = array_get($module, 'composer.extra.laravel-module.middleware', []);
        $controller = array_get($module, 'controller');

        // Controller路由 参数
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $action = camel_case(implode('_', [$method, array_first_plus(array_get($module, 'subfix')) ? : 'index']));

        // RESTful路由参数
        $resource = array_get($module, 'default') ? '' : array_get($module, 'name');

        // 路由列表
        $actions = [
            $action => [
                'prefix' => '',
                'route' => array_get($module, 'route'),
                'method' => [$method]
            ],
            'index' => [
                'prefix' => $resource,
                'route' => '/',
                'method' => ['get']
            ],
            'create' => [
                'prefix' => $resource,
                'route' => '/create',
                'method' => ['get']
            ],
            'store' => [
                'prefix' => $resource,
                'route' => '/',
                'method' => ['post']
            ],
            'show' => [
                'prefix' => $resource,
                'route' => '/{id}',
                'method' => ['get']
            ],
            'edit' => [
                'prefix' => $resource,
                'route' => '/{id}/edit',
                'method' => ['get']
            ],
            'update' => [
                'prefix' => $resource,
                'route' => '/{id}',
                'method' => ['put', 'patch']
            ],
            'destroy' => [
                'prefix' => $resource,
                'route' => '/{id}',
                'method' => ['delete']
            ]
        ];
        foreach ($actions as $action => $option) {
            if (method_exists($controller, $action)) {
                foreach ($option['method'] as $method) {
                    $route = $option['prefix'] . $option['route'];
                    $router->$method($route, ['uses' => "{$controller}@{$action}", 'middleware' => $middleware]);
                }
            }
        }
    },

    // 复制文件(支持闭包)
    'publishes' => [],

    // 命令配置(支持闭包)
    'commands' => [],

    // 扩展配置
    'macros' => [
        LaravelModule\Controllers\Controller::class => [
            'response' => function ($content = '', $status = 200, array $headers = []) {
                $class = class_exists(Illuminate\Routing\ResponseFactory::class) ?
                    Illuminate\Routing\ResponseFactory::class :
                    Laravel\Lumen\Http\ResponseFactory::class;

                $factory = app($class);

                if (func_num_args() === 0) {
                    return $factory;
                }

                return $factory->make($content, $status, $headers);
            },
            'request' => function ($key = null, $default = null) {
                if (is_null($key)) {
                    return app('request');
                }

                if (is_array($key)) {
                    return app('request')->only($key);
                }

                $value = app('request')->__get($key);

                return is_null($value) ? value($default) : $value;
            }
        ],
    ],

    // 自动加载
    'autoload' => function ($class) {
        if (starts_with($class, 'Module\\')) {
            $path = explode('\\', $class, 4);
            $name = explode('\\', array_pop($path));
            $file = base_path(
                implode(DIRECTORY_SEPARATOR, array_merge(array_map('snake_case', $path), ['src'], $name)) . '.php'
            );
            file_exists($file) && include_once $file;
        }
    },

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
