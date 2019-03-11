<?php

namespace Dev7studios\Surveyr\Console\Scheduling;

use Illuminate\Console\Scheduling\Event as BaseEvent;
use Illuminate\Contracts\Container\Container;
use GuzzleHttp\Client;

class Event extends BaseEvent
{
    /**
     * @var string
     */
    public $shouldMonitor = false;

    /**
     * @var string
     */
    protected $eventIdentifier = null;

    /**
     * @return void
     */
    public function monitor()
    {
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
    }

    /**
     * @param string $position
     * @return void
     */
    protected function reportEventToSurveyr($position)
    {
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
                    (new Client)->post(config('surveyr.url') . "/ping/{$appId}/{$monitorId}/{$position}", [
                        'json' => [
                            'event'  => $this->eventIdentifier,
                            'output' => $output,
                        ]
                    ]);
                } else {
                    (new Client)->get(config('surveyr.url') . "/ping/{$appId}/{$monitorId}/{$position}?event={$this->eventIdentifier}");
                }
            }, 500);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
