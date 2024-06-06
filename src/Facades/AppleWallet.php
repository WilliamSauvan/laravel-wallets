<?php

namespace Webqamdev\LaravelWallets\Facades;

use Illuminate\Support\Facades\Facade;

class AppleWallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'appleWallet';
    }
}
