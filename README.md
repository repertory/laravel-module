# laravel-module

Laravel模块化插件

```
composer require wangdong/laravel-module
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
