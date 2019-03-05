# Surveyr

Cron schedule monitoring for Laravel. For more info see [surveyr.io](https://surveyr.io/).

## Requirements

* Laravel 5.7+

## Install

Install this package via composer using this command:

```
composer require dev7studios/surveyr-laravel
```

The package will automatically register itself.

Publish the `config/surveyr.php` config file with:

```
php artisan vendor:publish --provider="Dev7studios\Surveyr\SurveyrServiceProvider"
```

Add the Surveyr credentials to your `.env` file:

```
SURVEYR_APP_ID=
SURVEYR_API_TOKEN=
```

If you don't already have one, you can create an API token via the [Surveyr settings page](http://surveyr.loc/settings#/api). The App ID can be found at the bottom of the relevant app page in [Surveyr](http://surveyr.loc/).

## Usage

To set up schedule cron job monitoring, first you need to specify which jobs you want to monitor in `app/Console/Kernel.php`. To do this simply add the `monitor()` method to any jobs you want Surveyr to monitor:

```
$schedule->command('example')
         ->everyMinute()
         ->monitor();
```

Then, to automatically create the schedule monitors in your Surveyr app, run the following command:

```
php artisan surveyr:sync-schedule-monitors
```

This command will attempt to create schedule monitors in Surveyr if they don't already exist. You can safely run this command during your deploy process to make sure that new scheduled jobs are monitored by Surveyr.

## Credits

[Surveyr](https://surveyr.io/) is a project by [Dev7studios](https://dev7studios.co/).
