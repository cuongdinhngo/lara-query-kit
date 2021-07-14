<?php

namespace Cuongnd88\LaraQueryKit;

use Illuminate\Support\ServiceProvider;

class LaraQueryKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Traits' => app_path(),
        ], 'app');
    }
}