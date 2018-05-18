<?php

if (!function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $subject)
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}

if (!function_exists('module_url')) {
    /**
     * 处理模块路由前缀问题
     * @param string $url
     * @return string
     */
    function module_url($url = '')
    {
        $url = trim($url, '/');
        if (!config('module.route.prefix')) {
            return '/' . $url;
        }
        $prefix = explode('/', trim(config('module.route.prefix'), '/'));
        $urls = explode('/', $url);
        $count = count($prefix);
        for ($i = 0; $i < $count; $i++) {
            if ($prefix[$i] == $urls[$i]) {
                $count--;
            }
        }
        if ($count) {
            for ($i = $count; $i > 0; $i--) {
                $urls = array_prepend($urls, $prefix[$i - 1]);
            }
        }
        return '/' . implode('/', $urls);
    }
}

if (!function_exists('module')) {
    /**
     * 获取模块信息
     * @param string $name
     * @param string $method
     * @return mixed|null
     * @throws ReflectionException
     */
    function module($name = '', $method = '')
    {
        static $modules = [];  // 优化多次获取模块信息

        $default = trim(config('module.route.default'), '/');
        $prefix = array_filter(explode('/', trim(config('module.route.prefix', ''), '/'))); // 支持前缀
        $url = $_SERVER ? parse_url($_SERVER["REQUEST_URI"]) : [];
        $path = trim($name ? module_url($name) : array_get($url, 'path', '/'), '/');
        // 过滤前缀不符合的模块
        if (count($prefix) && !starts_with($path, implode($prefix, '/'))) {
            return null;
        }

        $route = collect(array_filter(explode('/', $path)));
        $subfix = count($prefix) ? str_replace_first(implode($prefix, '/'), '', $path) : $path;

        // 处理默认模块
        if ($route->count() - count($prefix) < 2) {
            return $default ? module(join('/', [$default, trim($subfix, '/')])) : null;
        }

        // 模块名与当前url
        $name = "{$route->get(count($prefix))}/{$route->get(count($prefix) + 1)}";
        // 默认模块，路由加模块前缀
        if ($default == $name) {
            $url = trim(str_replace_first($name, '', $subfix), '/') ?: '/';
        } else {
            $url = $route->get(count($prefix) + 2) ? "{$name}/{$route->get(count($prefix) + 2)}" : $name;
        }
        // 请求方式，默认为get
        $method = $method ?: app('request')->method();
        $method = strtolower($method);
        // 控制器参数
        $group = studly_case(strtolower($route->get(count($prefix))));
        $module = studly_case(strtolower($route->get(count($prefix) + 1)));
        $action = $method . studly_case(strtolower($route->get(count($prefix) + 2, 'index')));
        // 静态变量中获取
        if (array_has($modules, "{$group}.{$module}.{$action}")) {
            return array_get($modules, "{$group}.{$module}.{$action}", null);
        }
        // 控制器类名
        $namespace = implode('\\', ['\\Module', $group, $module]);
        $controller = implode('\\', [$namespace, 'Controller']);
        if (!class_exists($controller)) {
            return null;
        }
        // 通过类名获取模块文件信息
        $object = new ReflectionClass($controller);      // 反解析类文件信息
        $path = dirname(dirname($object->getFileName())); // 模块根目录
        if (!file_exists(realpath($path . '/composer.json'))) {
            return null;
        }
        // 获取composer配置信息，同时验证模块类型
        $composer = json_decode(file_get_contents(realpath($path . '/composer.json')), true);
        if (array_get($composer, 'type') != 'laravel-module') {
            return null;
        }
        // 保存模块信息到静态变量
        array_set($modules, "{$group}.{$module}.{$action}", [
            'name' => $name,
            'route' => $url,
            'url' => module_url($url),
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
            'group' => $group,
            'module' => $module,
            'action' => $action,
            'composer' => $composer,
            'config' => [
                'view' => [
                    'path' => realpath($path . '/views'),
                ],
            ],
        ]);
        return array_get($modules, "{$group}.{$module}.{$action}", null);
    }
}

if (!function_exists('module_config')) {
    /**
     * 获取当前模块下的配置
     * @param null|string $key
     * @param mixed $default
     * @return \Illuminate\Config\Repository|mixed
     */
    function module_config($key = null, $default = null)
    {
        $module = module();
        $keys = array_merge(['module', 'modules'], explode('/', array_get($module, 'name', config('module.route.default'))));
        $config = config(implode('.', $keys), []);

        if (is_null($key)) {
            return $config;
        }

        return array_get($config, $key, $default);
    }
}

if (!function_exists('request_id')) {
    /**
     * 根据每次请求生成的唯一ID
     * @param mixed $group
     * @param string $prefix
     * @return string
     */
    function request_id($group = null, $prefix = '')
    {
        return implode('', [$prefix, md5(var_export([$_SERVER, $group], true))]);
    }
}
