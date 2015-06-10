<?php

namespace Quotebot\Handlers\Monitoring;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\ConsulHealthDaemon\ServicesChecker;

/**
 * This is invoked regularly to check service status
 */
class MonitoringHandler {

    public function __construct(ServicesChecker $services_checker) {
        $this->services_checker = $services_checker;
    }

    public function handleConsoleHealthCheck() {
        if (env('PROCESS_NAME', 'quotebot') == 'quotebotdaemon') {
            $this->handleConsoleHealthCheckForQuotebotDaemon();
        } else {
            $this->handleConsoleHealthCheckForQuotebot();
        }
    }

    public function handleConsoleHealthCheckForQuotebotDaemon() {
        // check MySQL
        $this->services_checker->checkMySQLConnection();

        // check pusher
        $this->services_checker->checkPusherConnection();
    }

    public function handleConsoleHealthCheckForQuotebot() {
        // check MySQL
        $this->services_checker->checkMySQLConnection();

        // check pusher
        $this->services_checker->checkPusherConnection();
    }

    public function subscribe($events) {
        $events->listen('consul-health.console.check', 'Quotebot\Handlers\Monitoring\MonitoringHandler@handleConsoleHealthCheck');
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Checks
    
}
