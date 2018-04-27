# laravel-module

[![Latest Stable Version](https://poser.pugx.org/wangdong/laravel-module/version)](https://packagist.org/packages/wangdong/laravel-module)
[![Total Downloads](https://poser.pugx.org/wangdong/laravel-module/downloads)](https://packagist.org/packages/wangdong/laravel-module)
[![Latest Unstable Version](https://poser.pugx.org/wangdong/laravel-module/v/unstable)](//packagist.org/packages/wangdong/laravel-module)
[![License](https://poser.pugx.org/wangdong/laravel-module/license)](https://packagist.org/packages/wangdong/laravel-module)
[![composer.lock available](https://poser.pugx.org/wangdong/laravel-module/composerlock)](https://packagist.org/packages/wangdong/laravel-module)

Laravel模块化插件

## 安装
```
# 安装扩展包
composer require wangdong/laravel-module

# 生成配置文件
php artisan vendor:publish --provider=LaravelModule\ServiceProvider
```

## 配置

> laravel目录创建`module`文件夹，然后修改`composer.json`

```
"autoload": {
  "classmap": [
    "module"
  ]
}
```

## 生成

> 生成默认模块

```
php artisan make:module module/index
```
