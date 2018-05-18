<?php

namespace LaravelModule;

use Parse\ParseClient;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{

    public function boot()
    {
        $path = dirname(__DIR__);  // 根路径

        if ($this->app->runningInConsole()) {
            // 复制文件
            $this->publishes([
                $path . '/config/module.php' => base_path('config/module.php'),
            ]);

            $this->commands([
                Commands\Make::class,
            ]);
        } else {
            $module = module();
            if ($module) {
                // 默认模块
                if (config('module.route.default')) {
                    $default = module(config('module.route.default'));
                    if ($default) {
                        $this->loadViewsFrom(array_get($default, 'config.view.path'), 'module.default');
                    }
                }
                // 当前模块
                $this->loadViewsFrom(array_get($module, 'config.view.path'), 'module');
                $group = [
                    'prefix' => config('module.route.prefix', ''),
                    'middleware' => config('module.route.middleware', [])
                ];

                // 兼容不同版本路由
                $router = $this->app['router'];
                if ($this->app instanceof LumenApplication && !property_exists($this->app, 'router')) {
                    $router = $this->app;
                }
                $router->group($group, function ($router) use ($module) {
                    $method = array_get($module, 'method');
                    $route = array_get($module, 'route');
                    $controller = array_get($module, 'controller');
                    $action = array_get($module, 'action');
                    $middleware = array_get($module, 'composer.extra.laravel-module.middleware', []);

                    // 验证控制器中对应方法是否存在，否则模块路由无效
                    if (method_exists($controller, $action)) {
                        $router->$method($route, ['uses' => "{$controller}@{$action}", 'middleware' => $middleware]);
                    }
                });
            }
        }

        // 初始化Parse
        $parse = config('module.parse', []);
        if (array_get($parse, 'app_id') && array_get($parse, 'server_url')) {
            ParseClient::initialize(array_get($parse, 'app_id'), array_get($parse, 'rest_key'), array_get($parse, 'master_key'));
            ParseClient::setServerURL(array_get($parse, 'server_url'), array_get($parse, 'mount_path'));
        }
    }

    public function register()
    {
        $path = dirname(__DIR__);  // 根路径
        $this->mergeConfigFrom($path . '/config/module.php', 'module');
    }

}
