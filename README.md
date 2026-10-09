Laravel SendCloud

SendCloud API SDK bindings for Laravel. This package does not register a Laravel Mail transport.

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/overtrue/laravel-sendcloud?style=flat-square)
![GitHub License](https://img.shields.io/github/license/overtrue/laravel-sendcloud?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/overtrue/laravel-sendcloud?style=flat-square)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

## Requirements

- PHP 8.3 or newer (PHP 8.x)
- Laravel 13.30 or newer (Laravel 13.x)
- SendCloud SDK 2.x

See [UPGRADE.md](UPGRADE.md) when upgrading from 1.x.

## Installing

```shell
$ composer require overtrue/laravel-sendcloud:^2.0
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

## Configuration and lifecycle

Additional SDK options such as `timeout`, `connect_timeout`, `response_type`, and `base_uri` can be added directly to `services.sendcloud`. Keep custom API endpoints on HTTPS. Credentials are checked when the client is resolved, so booting the application does not require them.

The class binding and `app('sendcloud')` create a fresh client on each resolution. Laravel facades cache their resolved client until Laravel clears the facade state; configuration changes do not update an already resolved client. Resolve a fresh client after changing configuration. Long-running workers must manage their application and facade lifecycle; Octane compatibility is not claimed by this package.

## Testing

```shell
composer install
composer validate --strict
composer lint
composer test
composer audit
```

Tests use Orchestra Testbench and a mocked HTTP transport. No SendCloud account or live API requests are needed. CI checks PHP 8.3, 8.4, and 8.5 plus the lowest secure dependency graph.

## Documentation

- [overtrue/sendcloud](https://github.com/overtrue/sendcloud) 
- [SendCloud Mail API v2](https://www.sendcloud.net/doc/email_v2/)


## :heart: Sponsor me 

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)


## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)

## License

MIT
