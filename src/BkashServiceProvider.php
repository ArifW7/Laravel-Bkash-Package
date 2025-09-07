<?php

namespace ArifW7\Bkash;

use Illuminate\Support\ServiceProvider;

class BkashServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/bkash.php' => config_path('bkash.php'),
        ], 'config');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bkash');

        // Optionally, publish views for override
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/bkash'),
        ], 'views');
    }

}
