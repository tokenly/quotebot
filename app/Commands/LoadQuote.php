<?php namespace Quotebot\Commands;

use Quotebot\Commands\Command;

class LoadQuote extends Command {

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($driver, $base, $target)
    {
        $this->driver = $driver;
        $this->base   = $base;
        $this->target = $target;
    }

}
