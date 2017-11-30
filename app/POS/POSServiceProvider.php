<?php namespace App\POS;

use Illuminate\Support\ServiceProvider;

class POSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the POS services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('pos', function($app) {

            return new POSManager($app);
        });

        $this->app->singleton('pos.driver', function($app) {

            return $app['pos']->driver();
        });
    }
}
