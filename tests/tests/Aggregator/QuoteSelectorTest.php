<?php

use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use \PHPUnit_Framework_Assert as PHPUnit;

class QuoteSelectorTest extends TestCase {

    protected $use_database = true;

    public function testRawQuoteSelection_basic()
    {
        $quote_helper = $this->app->make('QuoteHelper');
        list($name, $pair) = $quote_helper->getSampleNameAndPair();

        $t = 1423000000;
        $s = function($v) { return $v * 100000000; };

        $quotes = [
            [200,210,220, 0   ],
            [300,310,320, 1000],
        ];
        $this->populateDatabase($quotes);

        // get the quotes from the DB
        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+1000);
        PHPUnit::assertCount(2, $quotes);
        PHPUnit::assertEquals($s(210), $quotes[0]['last']);

        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+999);
        PHPUnit::assertCount(1, $quotes);
        PHPUnit::assertEquals($s(210), $quotes[0]['last']);
    }

    public function testAggregateQuoteSelection_basic()
    {
        $quote_helper = $this->app->make('QuoteHelper');
        list($name, $pair) = $quote_helper->getSampleNameAndPair();

        $t = 1423000000;
        $s = function($v) { return $v * 100000000; };

        $quotes = [
            ['a', [
                'low' => 
                [200,210,220],
                'avg' => 
                [300,310,320],
                'high' => 
                [400,410,420],
                'time' => 
                [1000, 2000],
            ]],
        ];
        $this->populateDatabase($quotes);

        // get the quotes from the DB
        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+1000);
        PHPUnit::assertCount(1, $quotes);
        PHPUnit::assertEquals($s(310), $quotes[0]['last_avg']);
    }

    public function testRawQuoteSelection_combined()
    {
        $quote_helper = $this->app->make('QuoteHelper');
        list($name, $pair) = $quote_helper->getSampleNameAndPair();

        $t = 1423000000;
        $s = function($v) { return $v * 100000000; };

        $quotes = [
            [200,210,220, 0   ],
            ['a', [
                'low' => 
                [200,210,220],
                'avg' => 
                [300,310,320],
                'high' => 
                [400,410,420],
                'time' => 
                [1000, 2000],
            ]],
            [300,310,320, 2000],
        ];
        $this->populateDatabase($quotes);

        // get the quotes from the DB
        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+2000);
        PHPUnit::assertCount(3, $quotes);
        PHPUnit::assertEquals($s(210), $quotes[0]['last']);
        PHPUnit::assertEquals($s(410), $quotes[1]['last_high']);
        PHPUnit::assertEquals($s(310), $quotes[2]['last']);

        $quotes = $this->getSelector()->findQuotesByTimestampRange($name, $pair, $t, $t+999);
        PHPUnit::assertCount(1, $quotes);
        PHPUnit::assertEquals($s(210), $quotes[0]['last']);
    }



    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function getSelector() {
        return $this->app->make('Quotebot\Quote\Selector');
    }

    protected function populateDatabase($quotes) {
        $quote_helper = $this->app->make('QuoteHelper');
        $raw_quote_repository = $this->app->make('Quotebot\Repositories\RawQuoteRepository');
        $aggregate_quote_repository = $this->app->make('Quotebot\Repositories\AggregateQuoteRepository');

        $quotes = $quote_helper->buildQuotesCollectionFromQuotesArray($quotes);
        foreach($quotes as $quote) {
            if ($quote instanceof RawQuote) { $raw_quote_repository->saveModel($quote); }
            else if ($quote instanceof AggregateQuote) { $aggregate_quote_repository->saveModel($quote); }
            else { throw new Exception("unknown quote type", 1); }
        }

    }




}
