<?php

namespace App\POS;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class POSServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('pos', function ($app) {
            return new POSManager($app);
        });

        $this->app->singleton('pos.driver', function ($app) {
            return $app['pos']->driver();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['pos', 'pos.driver'];
    }
}
