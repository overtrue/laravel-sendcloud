Laravel SendCloud

SendCloud Mail SDK for Laravel.

![Laravel Octane Ready Status](https://img.shields.io/badge/Octance-ready-green?style=flat-square)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/overtrue/laravel-sendcloud?style=flat-square)
![GitHub License](https://img.shields.io/github/license/overtrue/laravel-sendcloud?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/overtrue/laravel-sendcloud?style=flat-square)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

## Installing

```shell
$ composer require overtrue/laravel-sendcloud -vvv
```

## Usage

1. config your apiUser and apiKey into `config/services.php`: 

```php
    //...
    
    'sendcloud' => [
        'api_user' => env('SENDCLOUD_API_USER', ''),
        'api_key'  => env('SENDCLOUD_API_KEY', ''),
    ],
```

2. Call SendCloud API:

```php
$result = SendCloud::post('/mail/send', [
                'from' => 'demo@DKDJzmUzrxCESzdCu5R.sendcloud.org',
                'to' => 'demo@easywechat.com',
                'subject' => '来自 SendCloud 的第一封邮件！',
                'html' => '你太棒了！你已成功的 从SendCloud 发送了一封测试邮件！',
            ]);
            
// or 

$result = app('sendcloud')->get('addresslist/list');
```

## Documentation

- [overtrue/sendcloud](https://github.com/overtrue/sendcloud) 
- [SendCloud Mail API v2](http://www.sendcloud.net/doc/email_v2/)


## :heart: Sponsor me 

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)


## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
