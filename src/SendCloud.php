<?php

/*
 * This file is part of the overtrue/laravel-sendcloud.
 *
 * (c) overtrue <anzhengchao@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Overtrue\LaravelSendCloud;

use Illuminate\Support\Facades\Facade;

/**
 * Class SendCloud.
 *
 * @author overtrue <i@overtrue.me>
 */
class SendCloud extends Facade
{
    public static function getFacadeAccessor()
    {
        return \Overtrue\SendCloud\SendCloud::class;
    }
}
