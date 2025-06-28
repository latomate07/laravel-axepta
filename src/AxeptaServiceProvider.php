<?php

namespace TLM\LaravelAxepta;

use Illuminate\Support\ServiceProvider;

class AxeptaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/axepta.php', 'axepta'
        );

        $this->app->singleton(AxeptaService::class, function ($app) {
            return new AxeptaService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/axepta.php' => config_path('axepta.php'),
            ], 'axepta-config');
        }
    }
}