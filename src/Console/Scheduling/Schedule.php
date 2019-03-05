<?php

namespace Dev7studios\Surveyr\Console\Scheduling;

use Illuminate\Console\Scheduling\Schedule as BaseSchedule;

class Schedule extends BaseSchedule
{
    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Dev7studios\Surveyr\Console\Scheduling\Event
     */
    public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new Event($this->eventMutex, $command);

        return $event;
    }
}