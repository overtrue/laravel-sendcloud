<?php

/*
 * This file is part of the overtrue/laravel-sendcloud.
 *
 * (c) overtrue <anzhengchao@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Overtrue\LaravelSendCloud;

use Illuminate\Support\ServiceProvider;
use Overtrue\SendCloud\SendCloud;

/**
 * Class SendCloudServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class SendCloudServiceProvider extends ServiceProvider
{
    /**
     * Boot service.
     */
    public function boot()
    {
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(SendCloud::class, function ($app) {
            $config = config('services.sendcloud', []);

            if (empty($config['api_user']) || empty($config['api_key'])) {
                throw new \RuntimeException('No sendcloud configuration found.');
            }

            return new SendCloud($config['api_user'], $config['api_key'], $config);
        });

        $this->app->alias(SendCloud::class, 'sendcloud');
    }
}
