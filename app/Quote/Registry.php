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
            ['poloniex'      , ['BTC:LTBC'],],
        ];
    }

    public function allQuoteTypesIterator() {
        foreach ($this->allQuoteTypes() as $entry) {
            $name = $entry[0];
            foreach($entry[1] as $pair) {
                yield $name => $pair;
            }
        }
    }

}