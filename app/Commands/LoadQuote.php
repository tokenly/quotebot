<?php namespace Quotebot\Commands;

use Quotebot\Commands\Command;

class LoadQuote extends Command {

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($driver, $pairs)
    {
        $this->driver = $driver;
        $this->pairs  = $pairs;
    }

}
