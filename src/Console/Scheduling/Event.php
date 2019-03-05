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
     * @return void
     */
    public function monitor()
    {
        $this->shouldMonitor = true;

        $this->before(function () {
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

        $timezone   = $this->timezone ? $this->timezone : config('app.timezone');
        $identifier = sha1($this->command . $this->expression . $timezone);

        try {
            (new Client)->get(config('surveyr.url') . "/ping/{$appId}/{$identifier}/{$position}");
        } catch (\Exception $e) {
            report($e);
        }
    }
}
