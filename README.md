# laravel-module

[![Latest Stable Version](https://poser.pugx.org/wangdong/laravel-module/version)](https://packagist.org/packages/wangdong/laravel-module)
[![Total Downloads](https://poser.pugx.org/wangdong/laravel-module/downloads)](https://packagist.org/packages/wangdong/laravel-module)
[![Latest Unstable Version](https://poser.pugx.org/wangdong/laravel-module/v/unstable)](//packagist.org/packages/wangdong/laravel-module)
[![License](https://poser.pugx.org/wangdong/laravel-module/license)](https://packagist.org/packages/wangdong/laravel-module)
[![composer.lock available](https://poser.pugx.org/wangdong/laravel-module/composerlock)](https://packagist.org/packages/wangdong/laravel-module)

Laravel模块化插件

> 支持Laravel和Lumen版本 >= 5.1

## 安装
```
# 安装扩展包
composer require wangdong/laravel-module
```

## 初始化

**Laravel**
```
# laravel 5.1 - 5.4需文件`config/app.php`增加providers项
LaravelModule\ServiceProvider::class,

# 生成配置文件
php artisan module:publish
```

**Lumen**
```
# 文件`bootstrap/app.php`增加providers项
$app->register(LaravelModule\ServiceProvider::class);
```

## 开发

> 生成模块

```
php artisan module:init module/index
```
