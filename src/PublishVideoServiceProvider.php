<?php

namespace Qihucms\PublishVideo;

use Illuminate\Support\ServiceProvider;

class PublishVideoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'publishVideo');
    }
}
