<?php

namespace Dev7studios\Surveyr\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class SyncScheduleMonitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveyr:sync-schedule-monitors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync scheduled job monitors with Surveyr';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appId = config('surveyr.app_id');
        if (!$appId) {
            $this->error('Missing: SURVEYR_APP_ID');
            return;
        }

        $schedule = app()->make('Illuminate\Console\Scheduling\Schedule');

        if (!count($schedule->events())) {
            $this->info('No schedules found.');
            return;
        }

        $this->info(count($schedule->events()) . ' schedules found:');
        $this->line('');

        $client = new Client([
            'base_uri' => config('surveyr.url'),
            'headers'  => [
                'Accept' => 'application/json',
            ],
            'query' => [
                'api_token' => config('surveyr.api_token'),
            ],
        ]);

        foreach ($schedule->events() as $event) {
            if (!isset($event->shouldMonitor) || !$event->shouldMonitor) {
                continue;
            }

            $timezone = $event->timezone ? $event->timezone : config('app.timezone');

            $this->info('Creating schedule monitor...');
            if ($event->description) {
                $this->line('Name: ' . $event->description);
            }
            $this->line('Command: ' . $event->command);
            $this->line('Schedule: ' . $event->expression);
            $this->line('Timezone: ' . $timezone);

            try {
                $response = $client->request('POST', '/api/schedule-monitors', [
                    'json' => [
                        'identifier' => $appId,
                        'name'       => $event->description,
                        'command'    => $event->command,
                        'schedule'   => $event->expression,
                        'timezone'   => $timezone,
                    ],
                ]);

                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
                    $this->info('Schedule monitor created');
                } else {
                    $this->error('Error creating schedule monitor: ');
                    $this->line($response->getBody()->getContents());
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $json     = json_decode($response->getBody()->getContents());

                $this->error('Error creating schedule monitor: ');
                if (!empty($json->message)) {
                    $this->line($json->message);
                }
                if (!empty($json->errors)) {
                    foreach ($json->errors as $key => $errors) {
                        $this->line($key . ' - ' . implode(' / ', $errors));
                    }
                }

                if ($response->getStatusCode() == 402) {
                    $this->error('Upgrade required!');
                }
            } catch (\Exception $e) {
                report($e);

                $this->error('Error creating schedule monitor: ');
                $this->line($e->getMessage());
            }

            $this->line('');
        }
    }
}
