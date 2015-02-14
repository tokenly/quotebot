<?php namespace Quotebot\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'Quotebot\Console\Commands\Inspire',

        'Quotebot\Console\Commands\LoadQuote',
        'Quotebot\Console\Commands\FetchQuote',
        'Quotebot\Console\Commands\AggregateQuotes',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // every minute
        $load_cron = '* * * * *';
        $schedule->command('quotebot:load bitcoinAverage USD BTC ')->cron($load_cron);
        $schedule->command('quotebot:load bitstamp       USD BTC ')->cron($load_cron);
        $schedule->command('quotebot:load poloniex       BTC LTBC')->cron($load_cron);

        // 1 minute after the hour
        $aggregate_cron = '1 * * * *';
        $schedule->command('quotebot:aggregate-quotes bitcoinAverage USD BTC ')->cron($aggregate_cron);
        $schedule->command('quotebot:aggregate-quotes bitstamp       USD BTC ')->cron($aggregate_cron);
        $schedule->command('quotebot:aggregate-quotes poloniex       BTC LTBC')->cron($aggregate_cron);
    }

}
