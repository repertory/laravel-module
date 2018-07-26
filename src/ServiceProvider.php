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

        if ($this->app->runningInConsole()) {
            // 复制文件
            $this->publishes([
                $path . '/config/module.php' => base_path('config/module.php'),
            ]);
            $publishes = config('module.publishes', []);
            $this->publishes($publishes instanceof Closure ? $publishes() : $publishes);

            $this->commands([
                Commands\Init::class,
                Commands\Make::class,
                Commands\Publish::class,
            ]);
            $commands = config('module.commands', []);
            $this->commands($commands instanceof Closure ? $commands() : $commands);
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
                    call_user_func_array(config('module.router'), [$router, $module]);
                }
            }
        }
    }

    public function register()
    {
        // 自动加载模块中的类
        spl_autoload_register([$this, 'loadModule'], true, false);

        $path = dirname(__DIR__);  // 根路径
        $this->mergeConfigFrom($path . '/config/module.php', 'module');
    }

    public function loadModule($class)
    {
        if (starts_with($class, 'Module\\')) {
            $paths = explode('\\', $class, 4);
            $subfix = explode('\\', array_pop($paths));
            $file = base_path(implode(DIRECTORY_SEPARATOR, array_merge(array_map('snake_case', $paths), ['src'], $subfix)) . '.php');

            file_exists($file) && include_once $file;
            return true;
        }
    }

}
