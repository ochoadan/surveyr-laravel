<?php

namespace Dev7studios\Surveyr;

use Illuminate\Console\Scheduling\Event;
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

        $this->loadCommands();
        $this->addEventMacros();
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

    /**
     * Load the schedule monitor.
     *
     * @return void
     */
    protected function addEventMacros()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        Event::macro('reportEventToSurveyr', function($position) {
            $appId = config('surveyr.app_id');
            if (!$appId) {
                return;
            }

            $timezone  = $this->timezone ? $this->timezone : config('app.timezone');
            $monitorId = sha1($this->command . $this->expression . $timezone);

            $output = null;
            if ($position == 'finish' && $this->output) {
                $output = @file_get_contents($this->output);
            }

            try {
                retry(3, function () use ($appId, $monitorId, $position, $output) {
                    if ($position == 'finish') {
                        (new \GuzzleHttp\Client)->post(config('surveyr.url') . "/ping/{$appId}/{$monitorId}/{$position}", [
                            'json' => [
                                'event'  => $this->eventIdentifier,
                                'output' => $output,
                            ]
                        ]);
                    } else {
                        (new \GuzzleHttp\Client)->get(config('surveyr.url') . "/ping/{$appId}/{$monitorId}/{$position}?event={$this->eventIdentifier}");
                    }
                }, 500);
            } catch (\Exception $e) {
                report($e);
            }
        });

        Event::macro('monitor', function() {
            $this->shouldMonitor = true;

            $this->ensureOutputIsBeingCaptured();

            $this->before(function () {
                $this->eventIdentifier = sha1($this->expression . $this->command . microtime());
                $this->reportEventToSurveyr('start');
            });
            $this->after(function () {
                $this->reportEventToSurveyr('finish');
            });

            return $this;
        });
    }
}
