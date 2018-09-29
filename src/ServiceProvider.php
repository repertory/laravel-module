<?php

namespace LaravelModule;

use Closure;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{

    public function boot()
    {
        $path = dirname(__DIR__);  // 根路径

        // 支持macro扩展
        if (config('module.macros')) {
            foreach (config('module.macros') as $class => $macro) {
                if (class_exists($class) && method_exists($class, 'macro')) {
                    foreach ($macro as $method => $closure) {
                        app($class)->macro($method, $closure);
                    }
                }
            }
        }

        // 加载模块
        if ($this->app->runningInConsole()) {
            // 复制文件
            $this->publishes([
                $path . '/config/module.php' => base_path('config/module.php'),
            ]);
            $publishes = config('module.publishes', []);
            $this->publishes($publishes instanceof Closure ? call_user_func($publishes) : $publishes);

            $this->commands([
                Commands\Init::class,
                Commands\Make::class,
                Commands\Publish::class,
            ]);
            $commands = config('module.commands', []);
            $this->commands($commands instanceof Closure ? call_user_func($commands) : $commands);
        } else {
            $module = module();
            if ($module) {
                // 当前模块
                $this->loadViewsFrom(array_get($module, 'viewpath'), 'module');

                // 支持自定义路由
                if (config('module.router') instanceof Closure) {
                    // 兼容不同版本路由
                    if ($this->app instanceof LumenApplication && !property_exists($this->app, 'router')) {
                        $router = $this->app;
                    } else {
                        $router = $this->app['router'];
                    }
                    $group = [
                        'prefix' => config('module.route.prefix', ''),
                        'middleware' => config('module.route.middleware', [])
                    ];
                    $router->group($group, function ($router) use ($module) {
                        call_user_func_array(config('module.router'), [$router, $module]);
                    });
                }
            }
        }
    }

    public function register()
    {
        $path = dirname(__DIR__);  // 根路径
        $this->mergeConfigFrom($path . '/config/module.php', 'module');

        // 自动加载
        $autoload = config('module.autoload', '');
        if (is_array($autoload) || $autoload instanceof Closure) {
            spl_autoload_register($autoload, true, false);
        }
    }

}
