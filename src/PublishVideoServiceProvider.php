<?php

namespace Qihucms\PublishVideo;

use Illuminate\Support\ServiceProvider;
use Qihucms\PublishVideo\Commands\InstallCommand;
use Qihucms\PublishVideo\Commands\UninstallCommand;
use Qihucms\PublishVideo\Commands\UpgradeCommand;

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
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
                UpgradeCommand::class
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'publishVideo');
    }
}
