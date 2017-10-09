<?php

namespace T1k3\LaravelCalendarEvent;

use Illuminate\Console\Scheduling\Schedule;
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
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
//        $this->publishes([
//            __DIR__ . '/database/migrations' => database_path('migrations'),
//        ], 'migrations');

        $this->publishes([
            __DIR__ . '/config' => config_path(),
        ], 'config');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('generate:calendar-event')->hourly();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(GenerateCalendarEvent::class);

//        Package helpers
        if (file_exists($file = __DIR__ . '/Support/helpers.php')) require_once $file;
    }
}
