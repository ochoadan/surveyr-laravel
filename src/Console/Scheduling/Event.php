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

        $this->before(function () {
            $this->eventIdentifier = sha1($this->expression . $this->command . microtime());
            $this->reportEventToSurveyr('start');
        });
        $this->after(function () {
            $this->reportEventToSurveyr('finish');
        });
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

        try {
            (new Client)->get(config('surveyr.url') . "/ping/{$appId}/{$monitorId}/{$position}?event={$this->eventIdentifier}");
        } catch (\Exception $e) {
            report($e);
        }
    }
}
