<?php

namespace Webqamdev\LaravelWallets;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/wallets.php', 'wallets');
        $this->app->singleton('googleWallet', function ($app) {
            $config = $app->make('config')->get('wallets');

            return new Services\GoogleWalletService($config);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/wallets.php' => config_path('wallets.php'),
        ], 'config');
    }
}
