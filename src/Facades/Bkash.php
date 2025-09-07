<?php

namespace ArifW7\Bkash\Facades;

use Illuminate\Support\Facades\Facade;

class Bkash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ArifW7\Bkash\BkashClient::class;
    }
}
