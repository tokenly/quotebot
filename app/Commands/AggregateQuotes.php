<?php namespace Quotebot\Commands;

use Carbon\Carbon;
use Quotebot\Commands\Command;

class AggregateQuotes extends Command {

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($name, $base, $target, Carbon $start, Carbon $end)
    {
        $this->name   = $name;
        $this->base   = $base;
        $this->target = $target;
        $this->start  = $start;
        $this->end    = $end;
    }

}
