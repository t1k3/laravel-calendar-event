<?php

namespace T1k3\LaravelCalendarEvent;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use T1k3\LaravelCalendarEvent\Console\Commands\GenerateCalendarEvent;

/**
 * Class ServiceProvider
 * @package T1k3\LaravelCalendarEvent
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/config' => config_path(),
        ], 'config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(GenerateCalendarEvent::class);
    }
}
