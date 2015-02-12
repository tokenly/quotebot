<?php

namespace Quotebot\Providers\EventLog;

use Illuminate\Support\ServiceProvider;
use Quotebot\Providers\EventLog\EventLog;

class EventLogServiceProvider extends ServiceProvider {


    public function register() {
        $this->app->bind('eventlog', function($app) {
            // return new EventLog($app->make('InfluxDB\Client'));
            return new EventLog();
        });

    }


}
