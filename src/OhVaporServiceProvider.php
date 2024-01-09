<?php

namespace DouglasThwaites\OhVapor;

use DouglasThwaites\OhVapor\Console\Commands\OhVaporWafUpdateCommand;
use Illuminate\Support\ServiceProvider;

class OhVaporServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__.'/../config/oh-vapor.php' => config_path('oh-vapor.php')
        ], 'oh-vapor-config');

        // Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                OhVaporWafUpdateCommand::class
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