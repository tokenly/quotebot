<?php

use Carbon\Carbon;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use \PHPUnit_Framework_Assert as PHPUnit;

class DatabaseQuoteAggregratorTest extends TestCase {

    protected $use_database = true;

    public function testQuoteAggegation_single()
    {
        $quote_helper = $this->app->make('QuoteHelper');
        $db_aggregator = $this->app->make('Quotebot\Quote\DatabaseAggregator');

        $start_date = Carbon::create(2015, 2, 1, 0,0,0);
        $t = $start_date->getTimestamp();
        $quote_helper->setBaseTimestamp($t);

        list($name, $pair) = $quote_helper->getSampleNameAndPair();

        $s = function($v) { return $v * 100000000; };

        $quotes = [
            [200,210,220, 0   ],
            [300,310,320, 1800],
        ];
        $quote_helper->populateDatabaseWithQuotesArray($quotes);

        // aggregate 1 hour of quotes
        $db_aggregator->aggregateQuotesForTimeRange($name, $pair, $t, $t + 3600, '+1 hour');

        // get the quotes from the DB
        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+1000);
        // echo "\$quotes:\n".json_encode($quotes, 192)."\n";
        PHPUnit::assertNotEmpty($quotes);
        PHPUnit::assertCount(1, $quotes);
        PHPUnit::assertInstanceOf('Quotebot\Models\AggregateQuote', $quotes[0]);
    }


    public function testQuoteAggegation_double()
    {
        $quote_helper = $this->app->make('QuoteHelper');
        $db_aggregator = $this->app->make('Quotebot\Quote\DatabaseAggregator');

        $start_date = Carbon::create(2015, 2, 1, 0,0,0);
        $t = $start_date->getTimestamp();
        $quote_helper->setBaseTimestamp($t);

        list($name, $pair) = $quote_helper->getSampleNameAndPair();

        $s = function($v) { return $v * 100000000; };

        $quotes = [
            [200,210,220, 0   ],
            [300,310,320, 1800],
            [400,410,420, 3600],
            [500,510,520, 5400],
        ];
        $quote_helper->populateDatabaseWithQuotesArray($quotes);

        // aggregate 1 hour of quotes at a time
        $db_aggregator->aggregateQuotesForTimeRange($name, $pair, $t, $t + 7200, '+1 hour');

        // get the quotes from the DB
        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+7200);
        PHPUnit::assertNotEmpty($quotes);
        PHPUnit::assertCount(2, $quotes);
        PHPUnit::assertInstanceOf('Quotebot\Models\AggregateQuote', $quotes[0]);
        PHPUnit::assertInstanceOf('Quotebot\Models\AggregateQuote', $quotes[1]);
    }





    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function getSelector() {
        return $this->app->make('Quotebot\Quote\Selector');
    }


}
