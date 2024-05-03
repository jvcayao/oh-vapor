<?php

namespace DouglasThwaites\OhVapor;

use DouglasThwaites\OhVapor\Console\Commands\OhVaporSleepCommand;
use DouglasThwaites\OhVapor\Console\Commands\OhVaporWafUpdateCommand;
use Illuminate\Support\ServiceProvider;

class OhVaporServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish
        $this->publishes([
            __DIR__.'/config/oh-vapor.php' => config_path('oh-vapor.php'),
        ]);

        // Config
        $this->mergeConfigFrom(
            __DIR__.'/config/oh-vapor.php',
            'oh-vapor'
        );

        // Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                OhVaporWafUpdateCommand::class,
                OhVaporSleepCommand::class
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}