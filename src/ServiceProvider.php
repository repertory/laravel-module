<?php

namespace LaravelModule;

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
            $this->publishes(config('module.publishes', []));

            $this->commands([Commands\Make::class]);
            $this->commands(config('module.commands', []));
        } else {
            $module = module();
            if ($module) {
                // 默认模块
                if (config('module.route.default')) {
                    $default = module(config('module.route.default'));
                    if ($default) {
                        $this->loadViewsFrom(array_get($default, 'viewpath'), 'module.default');
                    }
                }
                // 当前模块
                $this->loadViewsFrom(array_get($module, 'viewpath'), 'module');
                $group = [
                    'prefix' => config('module.route.prefix', ''),
                    'middleware' => config('module.route.middleware', [])
                ];

                // 兼容不同版本路由
                if ($this->app instanceof LumenApplication && !property_exists($this->app, 'router')) {
                    $router = $this->app;
                } else {
                    $router = $this->app['router'];
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
    }

    public function register()
    {
        $path = dirname(__DIR__);  // 根路径
        $this->mergeConfigFrom($path . '/config/module.php', 'module');
    }

}
