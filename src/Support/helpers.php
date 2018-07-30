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
     * @param array $param
     * @return string
     * @throws ReflectionException
     */
    function module_url($url = '', $param = [])
    {
        $queryString = http_build_query($param);
        $url = trim($url, '/');
        if (!config('module.route.prefix') && !config('module.route.default')) {
            return '/' . $url . ($queryString ? '?' . $queryString : '');
        }
        $prefix = explode('/', trim(config('module.route.prefix', ''), '/'));
        $urls = explode('/', $url);
        $count = count($prefix);
        for ($i = 0; $i < count($prefix); $i++) {
            if ($prefix[$i] != $urls[$i]) {
                break;
            }
            $count--;
        }
        if ($count) {
            for ($i = $count; $i > 0; $i--) {
                $urls = array_prepend($urls, $prefix[$i - 1]);
            }
        }

        // 去除默认模块前缀
        $prefixCount = count($prefix);
        if (config('module.route.default') && (count($urls) - $prefixCount >= 2)) {
            $routeDefault = explode('/', trim(config('module.route.default'), '/'));

            if ($urls[$prefixCount] == $routeDefault[0] && $urls[$prefixCount + 1] == $routeDefault[1]) {
                array_splice($urls, $prefixCount, count($routeDefault));
            }
        }
        return '/' . implode('/', array_filter($urls)) . ($queryString ? '?' . $queryString : '');;
    }
}


if (!function_exists('module')) {
    /**
     * 获取模块信息
     * @param string $name
     * @return mixed|null
     * @throws ReflectionException
     */
    function module($name = '')
    {
        static $modules = [];  // 优化多次获取模块信息

        $default = trim(config('module.route.default'), '/');
        $prefix = array_filter(explode('/', trim(config('module.route.prefix', ''), '/'))); // 支持前缀
        $url = !empty($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : [];
        $path = trim($name ? : array_get($url, 'path', '/'), '/');

        // 前缀处理
        if (count($prefix) && !starts_with($path, implode($prefix, '/'))) {
            $path = module_url($path);
        }

        $route = collect(array_filter(explode('/', $path)));
        $subfix = count($prefix) ? str_replace_first(implode($prefix, '/'), '', $path) : $path;

        // 处理默认模块
        if ($route->count() - count($prefix) < 2) {
            return $default ? module(join('/', array_filter(array_merge([], $prefix, [$default], [trim($subfix, '/')])))) : null;
        }

        // 模块名与当前url
        $name = "{$route->get(count($prefix))}/{$route->get(count($prefix) + 1)}";
        // 默认模块，路由加模块前缀
        if ($default == $name) {
            $url = trim(str_replace_first($name, '', $subfix), '/') ? : '/';
        } else {
            $url = $route->get(count($prefix) + 2) ? "{$name}/{$route->slice(count($prefix) + 2)->implode('/')}" : $name;
        }

        // 控制器参数
        $group = studly_case(strtolower($route->get(count($prefix))));
        $module = studly_case(strtolower($route->get(count($prefix) + 1)));
        $subfix = $route->slice(count($prefix) + 2)->values()->toArray();
        // 静态变量中获取
        if (array_has($modules, $url)) {
            return array_get($modules, $url, null);
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
        array_set($modules, $url, [
            'name' => $name,
            'route' => $url,
            'subfix' => $subfix,
            'path' => $path,
            'controller' => $controller,
            'group' => $group,
            'module' => $module,
            'composer' => $composer,
            'viewpath' => realpath($path . '/views'),
        ]);
        return array_get($modules, $url, null);
    }
}

if (!function_exists('module_config')) {
    /**
     * 获取当前模块下的配置
     * @param null|string $key
     * @param mixed $default
     * @param string $name
     * @return \Illuminate\Config\Repository|mixed
     */
    function module_config($key = null, $default = null, $name = '')
    {
        $module = module($name);
        $keys = array_merge(['module', 'modules'], explode('/', array_get($module, 'name')));

        $moduleConfig = array_get($module, 'composer.extra.laravel-module.config', []);
        $config = array_merge($moduleConfig, config(implode('.', $keys), []));

        if (is_null($key)) {
            return $config;
        }

        return array_get($config, $key, $default);
    }
}

if (!function_exists('module_path')) {
    /**
     * 获取模块文件路径
     * @param string $name
     * @param string $path
     * @return string
     * @throws ReflectionException
     */
    function module_path($name = '', $path = '')
    {
        return array_get(module($name), 'path', '') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
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
        static $caches = [];

        $group = var_export($group, true);
        $cache = null;

        if (array_has($caches, $group)) {
            $cache = array_get($caches, $group);
        }

        if (!$cache) {
            $cache = md5(var_export([$_SERVER, $group], true));
            array_set($caches, $group, $cache);
        }

        return implode('', [$prefix, $cache]);
    }
}
