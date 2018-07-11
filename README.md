# laravel-module

[![Latest Stable Version](https://poser.pugx.org/wangdong/laravel-module/version)](https://packagist.org/packages/wangdong/laravel-module)
[![Total Downloads](https://poser.pugx.org/wangdong/laravel-module/downloads)](https://packagist.org/packages/wangdong/laravel-module)
[![Latest Unstable Version](https://poser.pugx.org/wangdong/laravel-module/v/unstable)](//packagist.org/packages/wangdong/laravel-module)
[![License](https://poser.pugx.org/wangdong/laravel-module/license)](https://packagist.org/packages/wangdong/laravel-module)
[![composer.lock available](https://poser.pugx.org/wangdong/laravel-module/composerlock)](https://packagist.org/packages/wangdong/laravel-module)

Laravel模块化插件

> 支持Laravel和Lumen版本 >= 5.1

## 配置

1. 安装

    ```
    composer require wangdong/laravel-module
    ```

2. 配置

  - Laravel配置(laravel >= 5.5 跳过此步骤)
    ```
    # 文件`config/app.php`增加providers项
    LaravelModule\ServiceProvider::class,
    ```

  - Lumen配置
    ```
    # 文件`bootstrap/app.php`增加providers项
    $app->register(LaravelModule\ServiceProvider::class);
    ```

3. 复制

    ```
    php artisan module:publish
    ```

## 开发

> 生成模块

```
php artisan module:init module/index
```
