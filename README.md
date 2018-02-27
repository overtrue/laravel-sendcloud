<h1 align="center">Laravel SendCloud</h1>

<p align="center"> SendCloud Mail SDK for Laravel.</p>

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

## License

MIT
