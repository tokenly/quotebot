<?php

use \PHPUnit_Framework_Assert as PHPUnit;

class RawQuoteRepositoryTest extends TestCase {

    protected $use_database = true;

    public function testLoadRawQuotes()
    {
        $helper = $this->createRepositoryTestHelper();

        $helper->testLoad();
        $helper->cleanup()->testUpdate(['name' => 'foo']);
        $helper->cleanup()->testDelete();
        $helper->cleanup()->testFindAll();
    }


    protected function createRepositoryTestHelper() {
        $quote_helper = $this->app->make('QuoteHelper');
        $ts_counter = 0;
        $create_model_fn = function() use ($quote_helper, &$ts_counter) {
            return $quote_helper->newSampleQuote(['timestamp' => $quote_helper->base_timestamp + ($ts_counter++)]);
        };
        $helper = new RepositoryTestHelper($create_model_fn, $this->app->make('Quotebot\Repositories\RawQuoteRepository'));
        return $helper;
    }

}
