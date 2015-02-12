<?php

use Quotebot\Repositories\Helper\DateHelper;
use \PHPUnit_Framework_Assert as PHPUnit;

class QuoteAggregatorTest extends TestCase {

    protected $use_database = false;

    public function testAggregateQuotes_basic()
    {
        $test_data = [
            'prevQuote' => 
                [200,210,220, 0   ],
            'quotes' => [
                [200,210,220, 0   ],
                [300,310,320, 1000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [250,260,270],
                'high' => 
                [300,310,320],
            ],

            'endTime' => 2000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }

    public function testAggregateQuotes_endsOnTimestamp()
    {
        $test_data = [
            'prevQuote' => 
                [200,210,220, 0   ],
            'quotes' => [
                [200,210,220, 0   ],
                [300,310,320, 1000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [200,210,220],
                'high' => 
                [300,310,320],
            ],

            'endTime' => 1000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }

    public function testAggregateQuotes_prevQuoteEarly()
    {
        $test_data = [
            'prevQuote' => 
                [100,110,120, -50 ],
            'quotes' => [
                [200,210,220, 0   ],
                [300,310,320, 1000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [200,210,220],
                'high' => 
                [300,310,320],
            ],

            'endTime' => 1000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }

    public function testAggregateQuotes_firstQuoteLate()
    {
        $test_data = [
            'prevQuote' => 
                [100,110,120, -50 ],
            'quotes' => [
                [200,210,220, 5000 ],
                [400,410,420, 10000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [150,160,170],
                'high' => 
                [400,410,420],
            ],

            'endTime' => 10000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }


    public function testAggregateQuotes_noPrevQuoteFirstQuoteLate()
    {
        $test_data = [
            'prevQuote' => null,
            'quotes' => [
                [200,210,220, 5000 ],
                [400,410,420, 10000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [300,310,320],
                'high' => 
                [400,410,420],
            ],

            'endTime' => 15000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }


    public function testAggregateQuotes_weighted()
    {
        $test_data = [
            'prevQuote' => 
                [200,210,220, 0   ],
            'quotes' => [
                [200,210,220, 0   ],
                [400,410,420, 2000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [350,360,370],
                'high' => 
                [400,410,420],
            ],

            'endTime' => 8000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }


    public function testAggregateQuotes_combined()
    {
        $test_data = [
            'prevQuote' => 
                [200,210,220, 0   ],
            'quotes' => [
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

                [400,410,420, 2000],
            ],
            'expected' => [
                'low' => 
                [200,210,220],
                'avg' => 
                [300,310,320],
                'high' => 
                [400,410,420],
            ],

            'endTime' => 3000,
        ];
        $this->runAggregateQuoteTestFromData($test_data);
    }


    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////


    protected function runAggregateQuoteTestFromData($test_data) {
        $quote_helper = $this->app->make('QuoteHelper');

        $t = 1423000000;
        $s = function($v) { return $v * 100000000; };

        $previous_quote = $test_data['prevQuote'] === null ? null : $quote_helper->buildRawQuote($test_data['prevQuote']);
        $quotes = $quote_helper->buildQuotesCollectionFromQuotesArray($test_data['quotes']);

        $aggregator = $this->app->make('Quotebot\Quote\Aggregator');
        $aggregate_quote = $aggregator->aggregateQuotes($quotes, $t, $t + $test_data['endTime'], $previous_quote);
        PHPUnit::assertInstanceOf('Quotebot\Models\AggregateQuote', $aggregate_quote);


        $e = $test_data['expected'];
        PHPUnit::assertEquals($s($e['low'][0]), $aggregate_quote['bid_low'], "Unexpected value for bid_low.");
        PHPUnit::assertEquals($s($e['avg'][0]), $aggregate_quote['bid_avg'], "Unexpected value for bid_avg.");
        PHPUnit::assertEquals($s($e['high'][0]), $aggregate_quote['bid_high'], "Unexpected value for bid_high.");

        PHPUnit::assertEquals($s($e['low'][1]), $aggregate_quote['last_low'], "Unexpected value for last_low.");
        PHPUnit::assertEquals($s($e['avg'][1]), $aggregate_quote['last_avg'], "Unexpected value for last_avg.");
        PHPUnit::assertEquals($s($e['high'][1]), $aggregate_quote['last_high'], "Unexpected value for last_high.");

        PHPUnit::assertEquals($s($e['low'][2]), $aggregate_quote['ask_low'], "Unexpected value for ask_low.");
        PHPUnit::assertEquals($s($e['avg'][2]), $aggregate_quote['ask_avg'], "Unexpected value for ask_avg.");
        PHPUnit::assertEquals($s($e['high'][2]), $aggregate_quote['ask_high'], "Unexpected value for ask_high.");
    
        PHPUnit::assertEquals($t, DateHelper::toTimestamp($aggregate_quote['start_timestamp']));
        PHPUnit::assertEquals($t + $test_data['endTime'], DateHelper::toTimestamp($aggregate_quote['end_timestamp']));
    }




}
