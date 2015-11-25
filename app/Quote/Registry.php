<?php

namespace Quotebot\Quote;

use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use Quotebot\Quote\Aggregator;
use Quotebot\Repositories\AggregateQuoteRepository;
use Quotebot\Repositories\Helper\DateHelper;
use Quotebot\Repositories\RawQuoteRepository;

/**
* 
*/
class Registry
{
    
    function __construct() {
    }

    public function allQuoteTypes() {
        return [
            ['bitcoinAverage', ['USD:BTC'], ],
            ['bitstamp'      , ['USD:BTC'], ],
            ['poloniex'      , ['BTC:LTBC'], ['BTC:LTBCOIN'],],
            ['poloniex'      , ['BTC:FLDC'],],
            ['poloniex'      , ['BTC:GEMZ'],],
            ['poloniex'      , ['BTC:SWARM'],],
            ['poloniex'      , ['BTC:SJCX'],],
            ['poloniex'      , ['BTC:XCP'],],
            ['poloniex'      , ['BTC:BCY'], ['BTC:BITCRYSTALS'],],
        ];
    }

    public function allQuoteTypesIterator() {
        foreach ($this->allQuoteTypes() as $entry) {
            $name = $entry[0];
            $aliases = isset($entry[2]) ? $entry[2] : [];
            foreach($entry[1] as $offset => $pair) {
                $alias = $aliases ? $aliases[$offset] : null;
                yield $name => [$pair, $alias];
            }
        }
    }

}