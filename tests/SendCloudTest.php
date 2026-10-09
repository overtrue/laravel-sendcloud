<?php

namespace Overtrue\LaravelSendCloud\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\PackageManifest;
use Orchestra\Testbench\TestCase;
use Overtrue\LaravelSendCloud\SendCloud as Facade;
use Overtrue\LaravelSendCloud\SendCloudServiceProvider;
use Overtrue\SendCloud\SendCloud;
use PHPUnit\Framework\Attributes\DataProvider;

class SendCloudTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SendCloudServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('services.sendcloud', [
            'api_user' => 'test-user',
            'api_key' => 'test-key',
        ]);
    }

    private function mockTransport(SendCloud $client, array $responses, array &$history): void
    {
        // Replace only the terminal transport, preserving the real SDK middleware.
        $stack = $client->getHandlerStack();
        $stack->setHandler(new MockHandler($responses));
        $stack->push(Middleware::history($history));
    }

    public function test_package_discovery_registers_provider_and_facade_alias(): void
    {
        $path = sys_get_temp_dir().'/laravel-sendcloud-'.bin2hex(random_bytes(8));
        $files = new Filesystem();
        $files->makeDirectory($path.'/vendor/composer', 0755, true);
        try {
            $package = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
            $files->put($path.'/vendor/composer/installed.json', json_encode(['packages' => [$package]]));
            $manifest = new PackageManifest($files, $path, $path.'/packages.php');
            $manifest->vendorPath = $path.'/vendor';
            self::assertSame([SendCloudServiceProvider::class], $manifest->providers());
            self::assertSame(['SendCloud' => Facade::class], $manifest->aliases());
            self::assertTrue($this->app->providerIsLoaded($manifest->providers()[0]));
            AliasLoader::getInstance($manifest->aliases())->register();
            self::assertTrue(is_a('SendCloud', Facade::class, true));
            self::assertInstanceOf(SendCloud::class, \SendCloud::getFacadeRoot());
        } finally {
            $files->deleteDirectory($path);
        }
    }

    public function test_class_and_string_bindings_are_transient_and_read_updated_config(): void
    {
        $first = $this->app->make(SendCloud::class);
        self::assertInstanceOf(SendCloud::class, $this->app->make('sendcloud'));
        self::assertNotSame($first, $this->app->make(SendCloud::class));
        $this->app['config']->set('services.sendcloud.api_key', 'updated-key');
        $second = $this->app->make('sendcloud');
        $history = [];
        $this->mockTransport($second, [new Response(200, [], '{}')], $history);
        $second->get('addresslist/list');
        parse_str($history[0]['request']->getUri()->getQuery(), $query);
        self::assertSame('updated-key', $query['apiKey']);
        self::assertSame('test-key', $first->getConfig()->getOption('api_key'));
    }

    public static function invalidConfigurations(): array
    {
        return [[[]], [['api_user' => 'user']], [['api_key' => 'key']], [['api_user' => '', 'api_key' => 'key']]];
    }

    #[DataProvider('invalidConfigurations')]
    public function test_credentials_are_validated_only_on_resolution(array $config): void
    {
        $this->app['config']->set('services.sendcloud', $config);
        (new SendCloudServiceProvider($this->app))->register();
        self::assertTrue($this->app->bound('sendcloud'));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No sendcloud configuration found.');
        $this->app->make('sendcloud');
    }

    public function test_configuration_is_forwarded_to_the_real_sdk(): void
    {
        foreach (['timeout' => 7, 'connect_timeout' => 2, 'response_type' => 'object'] as $key => $value) {
            $this->app['config']->set('services.sendcloud.'.$key, $value);
        }
        $this->app['config']->set('services.sendcloud.base_uri', 'https://example.test/custom/');
        $client = $this->app->make('sendcloud');
        $history = [];
        $this->mockTransport($client, [new Response(200, [], '{"result":true}')], $history);
        self::assertTrue($client->get('/addresslist/list')->result);
        self::assertSame('https://example.test/custom/addresslist/list', (string) $history[0]['request']->getUri()->withQuery(''));
        self::assertSame(7, $history[0]['options']['timeout']);
        self::assertSame(2, $history[0]['options']['connect_timeout']);
    }

    public function test_facade_dispatch_preserves_https_api_path_form_auth_and_response(): void
    {
        $client = Facade::getFacadeRoot();
        $history = [];
        $this->mockTransport($client, [new Response(200, [], '{"result":true,"statusCode":200}')], $history);
        self::assertSame(['result' => true, 'statusCode' => 200], Facade::post('/mail/send', ['subject' => 'Test']));
        $request = $history[0]['request'];
        self::assertSame('https://api.sendcloud.net/apiv2/mail/send', (string) $request->getUri());
        parse_str((string) $request->getBody(), $form);
        self::assertSame(['subject' => 'Test', 'apiUser' => 'test-user', 'apiKey' => 'test-key'], $form);
        self::assertFalse($history[0]['options']['allow_redirects']);
    }

    public function test_get_auth_preserves_unrelated_query_parameters(): void
    {
        $client = $this->app->make('sendcloud');
        $history = [];
        $this->mockTransport($client, [new Response(200, [], '{}')], $history);
        $client->get('/addresslist/list?offset=10&apiKey=untrusted');
        $request = $history[0]['request'];
        self::assertSame('/apiv2/addresslist/list', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame(['offset' => '10', 'apiUser' => 'test-user', 'apiKey' => 'test-key'], $query);
    }

    public function test_resolution_uses_its_own_container_even_when_another_is_globally_active(): void
    {
        $original = \Illuminate\Container\Container::getInstance();
        $other = new \Illuminate\Container\Container();
        $other->instance('config', new \Illuminate\Config\Repository([
            'services' => ['sendcloud' => ['api_user' => 'other-user', 'api_key' => 'other-key']],
        ]));
        try {
            \Illuminate\Container\Container::setInstance($other);
            $client = $this->app->make('sendcloud');
            self::assertSame('test-key', $client->getConfig()->getOption('api_key'));
        } finally {
            \Illuminate\Container\Container::setInstance($original);
        }
    }

    public function test_api_failure_payload_is_not_converted_to_an_exception(): void
    {
        $client = $this->app->make('sendcloud');
        $history = [];
        $this->mockTransport($client, [new Response(200, [], '{"result":false,"statusCode":400}')], $history);
        self::assertSame(['result' => false, 'statusCode' => 400], $client->get('addresslist/list'));
    }

    public function test_http_errors_remain_sdk_exceptions(): void
    {
        $client = $this->app->make('sendcloud');
        $history = [];
        $this->mockTransport($client, [new Response(401, [], '{}')], $history);
        $this->expectException(ClientException::class);
        $client->get('addresslist/list');
    }

    public function test_application_refresh_resets_the_facade_and_configuration(): void
    {
        $first = Facade::getFacadeRoot();
        $this->refreshApplication();
        $this->app['config']->set('services.sendcloud.api_key', 'next-application-key');
        $second = Facade::getFacadeRoot();
        self::assertNotSame($first, $second);
        $history = [];
        $this->mockTransport($second, [new Response(200, [], '{}')], $history);
        Facade::get('addresslist/list');
        parse_str($history[0]['request']->getUri()->getQuery(), $query);
        self::assertSame('next-application-key', $query['apiKey']);
    }
}
