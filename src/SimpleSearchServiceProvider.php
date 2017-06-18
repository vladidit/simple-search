<?php

namespace Vladidit\SimpleSearch;

use Illuminate\Support\ServiceProvider;

class SimpleSearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/assets/' => public_path('packages/simplesearch/'),
        ], 'simple_search.assets');

        $this->publishes([
            __DIR__.'/config/simple_search.php' => config_path('simple_search.php'),
        ], 'simple_search.config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
       //
    }
}
