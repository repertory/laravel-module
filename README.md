# laravel-module

Laravel模块化插件

```
# 安装扩展包
composer require wangdong/laravel-module

# 生成配置文件
php artisan vendor:publish --provider=LaravelModule\ServiceProvider
```

> laravel目录创建`module`文件夹，然后修改`composer.json`

```
"autoload": {
  "classmap": [
    "module"
  ]
}
```

> 生成默认模块

```
php artisan make:module module/index
```
