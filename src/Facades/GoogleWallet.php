<?php

namespace Webqamdev\LaravelWallets\Facades;

use Illuminate\Support\Facades\Facade;

class GoogleWallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'googleWallet';
    }
}
