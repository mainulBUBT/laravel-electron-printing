<?php

namespace LaravelElectronPrinting;

use Illuminate\Support\ServiceProvider;

class ElectronPrintingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/electron-printing.php', 'electron-printing'
        );

        // Register singleton
        $this->app->singleton('electron-print', function ($app) {
            return new ElectronPrintService(
                config('electron-printing.service_url'),
                config('electron-printing.timeout'),
                config('electron-printing.enabled'),
                config('electron-printing.use_websocket', false)
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/electron-printing.php' => config_path('electron-printing.php'),
        ], 'electron-printing-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'electron-printing-migrations');

        // Publish Electron app
        $this->publishes([
            __DIR__.'/../electron-app' => base_path('electron-print-service'),
        ], 'electron-printing-app');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\TestPrintCommand::class,
            ]);
        }
    }
}
