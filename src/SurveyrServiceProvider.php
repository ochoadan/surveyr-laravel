<?php

namespace Dev7studios\Surveyr;

use Dev7studios\Surveyr\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class SurveyrServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfig();
    }

    /**
     * Load the config file.
     *
     * @return void
     */
    protected function loadConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/surveyr.php',
            'surveyr'
        );
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();

        $this->loadScheduleMonitor();
        $this->loadCommands();
    }

    /**
     * Publish the config file.
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/config/surveyr.php' => config_path('surveyr.php'),
        ]);
    }

    /**
     * Load the schedule monitor.
     *
     * @return void
     */
    protected function loadScheduleMonitor()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->app->extend('Illuminate\Console\Scheduling\Schedule', function () {
            return new Schedule;
        });
    }

    /**
     * Load the commands.
     *
     * @return void
     */
    protected function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Dev7studios\Surveyr\Console\Commands\SyncScheduleMonitors::class,
            ]);
        }
    }
}
