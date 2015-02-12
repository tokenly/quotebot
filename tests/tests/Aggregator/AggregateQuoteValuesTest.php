<?php

use Carbon\Carbon;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use Quotebot\Repositories\Helper\DateHelper;
use \PHPUnit_Framework_Assert as PHPUnit;

class AggregateQuoteValuesTest extends TestCase {

    protected $use_database = true;

    public function testQuoteAggegationValues()
    {
        $hr = 3600;

        $test_data_collection = [
            [ ############################################################
                'quotes' => [
                    [200,210,220, 0   ],
                    [300,310,320, 1*$hr],
                ],
                'aggregate' => ['startTime' => 0, 'endTime' => 1*$hr,],
                'select'    => ['startTime' => 0, 'endTime' => 2*$hr,],
                'expected' => [
                    'low' => 
                    [200,210,220],
                    'avg' => 
                    [250,260,270],
                    'high' => 
                    [300,310,320],
                ],
            ], ############################################################
            [ ############################################################
                'quotes' => [
                    [200,210,220, 0   ],
                    [300,310,320, 1*$hr],
                ],
                'aggregate' => ['startTime' => 0, 'endTime' => 1*$hr,],
                'select'    => ['startTime' => 0, 'endTime' => 4*$hr,],
                'expected' => [
                    'low' => 
                    [200,210,220],
                    'avg' => 
                    [275,285,295],
                    'high' => 
                    [300,310,320],
                ],
            ], ############################################################
            [ ############################################################
                'quotes' => [
                    [200,210,220, -1],
                    [300,310,320, 1*$hr],
                    [400,410,420, 4*$hr+1],
                ],
                'aggregate' => ['startTime' => 0, 'endTime' => 1*$hr,],
                'select'    => ['startTime' => 0, 'endTime' => 4*$hr,],
                'expected' => [
                    'low' => 
                    [300,310,320],
                    'avg' => 
                    [275,285,295],
                    'high' => 
                    [300,310,320],
                ],
            ], ############################################################
            [ ############################################################
              # already aggregated
                'quotes' => [
                    [200,210,220, 0   ],
                    [300,310,320, 0.5*$hr],
                    [400,410,420, 1*$hr],
                    [500,510,520, 2*$hr],

                    ['a', [
                        'low' => 
                        [201,211,221],
                        'avg' => 
                        [251,261,271],
                        'high' => 
                        [301,311,321],
                        'time' => 
                        [0, 1*$hr],
                    ]],
                    ['a', [
                        'low' => 
                        [201,211,221],
                        'avg' => 
                        [251,261,271],
                        'high' => 
                        [301,311,321],
                        'time' => 
                        [1*$hr, 2*$hr],
                    ]],

                ],
                'aggregate' => ['startTime' => 0, 'endTime' => 1*$hr,],
                'select'    => ['startTime' => 0, 'endTime' => 1*$hr,],
                'expected' => [
                    'low' => 
                    [201,211,221],
                    'avg' => 
                    [251,261,271],
                    'high' => 
                    [301,311,321],
                ],
            ], ############################################################
        ];
        $this->runAggregateQuoteValuesTestFromMultipleData($test_data_collection);
    }





    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function getSelector() {
        return $this->app->make('Quotebot\Quote\Selector');
    }


    protected function runAggregateQuoteValuesTestFromData($test_data) {
        // setup
        $quote_helper = $this->app->make('QuoteHelper');
        $db_aggregator = $this->app->make('Quotebot\Quote\DatabaseAggregator');
        $selector = $this->app->make('Quotebot\Quote\Selector');
        $t = Carbon::create(2015, 2, 1, 0,0,0)->getTimestamp();
        $quote_helper->setBaseTimestamp($t);
        list($name, $pair) = $quote_helper->getSampleNameAndPair();
        $s = function($v) { return $v * 100000000; };

        // insert the quotes into the DB
        $quote_helper->populateDatabaseWithQuotesArray($test_data['quotes']);

        // aggregate quotes in the database
        $chunk_modification = isset($test_data['chunkSize']) ? $test_data['chunkSize'] : '+1 hour';
        $db_aggregator->aggregateQuotesForTimeRange($name, $pair, $t + $test_data['aggregate']['startTime'], $t + $test_data['aggregate']['endTime'], $chunk_modification);

        // now select the quotes from the database
        $aggregate_quote = $selector->buildAggregateQuoteByTimestampRange($name, $pair, $t + $test_data['select']['startTime'], $t + $test_data['select']['endTime']);
        PHPUnit::assertInstanceOf('Quotebot\Models\AggregateQuote', $aggregate_quote);


        $e = $test_data['expected'];
        PHPUnit::assertEquals($s($e['low'][0]), intval($aggregate_quote['bid_low']), "Unexpected value for bid_low.");
        PHPUnit::assertEquals($s($e['avg'][0]), intval($aggregate_quote['bid_avg']), "Unexpected value for bid_avg.");
        PHPUnit::assertEquals($s($e['high'][0]), intval($aggregate_quote['bid_high']), "Unexpected value for bid_high.");

        PHPUnit::assertEquals($s($e['low'][1]), intval($aggregate_quote['last_low']), "Unexpected value for last_low.");
        PHPUnit::assertEquals($s($e['avg'][1]), intval($aggregate_quote['last_avg']), "Unexpected value for last_avg.");
        PHPUnit::assertEquals($s($e['high'][1]), intval($aggregate_quote['last_high']), "Unexpected value for last_high.");

        PHPUnit::assertEquals($s($e['low'][2]), intval($aggregate_quote['ask_low']), "Unexpected value for ask_low.");
        PHPUnit::assertEquals($s($e['avg'][2]), intval($aggregate_quote['ask_avg']), "Unexpected value for ask_avg.");
        PHPUnit::assertEquals($s($e['high'][2]), intval($aggregate_quote['ask_high']), "Unexpected value for ask_high.");
    
        PHPUnit::assertEquals($t + $test_data['select']['startTime'], DateHelper::toTimestamp($aggregate_quote['start_timestamp']));
        PHPUnit::assertEquals($t + $test_data['select']['endTime'], DateHelper::toTimestamp($aggregate_quote['end_timestamp']));
    }

    protected function runAggregateQuoteValuesTestFromMultipleData($test_data_collection) {
        $quote_helper = $this->app->make('QuoteHelper');
        foreach($test_data_collection as $offset => $test_data) {
            try {
                // cleanup
                $quote_helper->cleanup();
                
                // run test
                $this->runAggregateQuoteValuesTestFromData($test_data);
            } catch (Exception $e) {
                print "\n************************************\n* Failed running test ".($offset+1)."\n************************************\n";
                throw $e;
            }
        }
    }


}
