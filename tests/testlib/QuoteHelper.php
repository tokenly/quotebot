<?php

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use Quotebot\Repositories\AggregateQuoteRepository;
use Quotebot\Repositories\RawQuoteRepository;

class QuoteHelper  {

    var $base_timestamp = 1423000000;

    function __construct(Application $app, RawQuoteRepository $quote_repository, AggregateQuoteRepository $aggregate_quote_repository) {
        $this->app                        = $app;
        $this->quote_repository           = $quote_repository;
        $this->aggregate_quote_repository = $aggregate_quote_repository;
    }

    public function setBaseTimestamp($base_timestamp) {
        $this->base_timestamp = $base_timestamp;
        return $this;
    }

    public function getSampleNameAndPair() {
        return ["bitcoinAverage", "USD:BTC"];
    }

    public function sampleQuoteVars() {
        return [
            'name'      => "bitcoinAverage",
            'pair'      => "USD:BTC",
            'bid'       => 21719000000,
            'last'      => 21739000000,
            'ask'       => 21755000000,
            'timestamp' => Carbon::createFromTimestamp(1423163845),
        ];
    }
    public function sampleAggregateQuoteVars() {
        return [
            'name'            => "bitcoinAverage",
            'pair'            => "USD:BTC",

            'bid_low'         => 20000000000,
            'bid_avg'         => 20500000000,
            'bid_high'        => 21000000000,

            'last_low'        => 21000000000,
            'last_avg'        => 21500000000,
            'last_high'       => 22000000000,

            'ask_low'         => 22000000000,
            'ask_avg'         => 22500000000,
            'ask_high'        => 23000000000,

            'start_timestamp' => Carbon::createFromTimestamp(1423163000),
            'end_timestamp'   => Carbon::createFromTimestamp(1423164000),
        ];
    }


    public function buildQuotesCollectionFromQuotesArray($quotes_data) {
        $t = $this->base_timestamp;
        $s = function($v) { return $v * 100000000; };
        $q = function($vars) { return $this->newRawQuoteModel($vars); };
        $aq = function($vars) { return $this->newAggregateQuoteModel($vars); };

        foreach ($quotes_data as $qd) {
            if ($qd[0] === 'a') {
                $aqd = $qd[1];
                $agg_quote = $aq([
                    'bid_low'         => $s($aqd['low'][0]),
                    'bid_avg'         => $s($aqd['avg'][0]),
                    'bid_high'        => $s($aqd['high'][0]),

                    'last_low'        => $s($aqd['low'][1]),
                    'last_avg'        => $s($aqd['avg'][1]),
                    'last_high'       => $s($aqd['high'][1]),

                    'ask_low'         => $s($aqd['low'][2]),
                    'ask_avg'         => $s($aqd['avg'][2]),
                    'ask_high'        => $s($aqd['high'][2]),

                    'start_timestamp' => $t + $aqd['time'][0],
                    'end_timestamp'   => $t + $aqd['time'][1],
                ]);
                $quotes[] = $agg_quote;
            } else {
                $quotes[] = $q(['bid' => $s($qd[0]), 'last' => $s($qd[1]), 'ask' => $s($qd[2]), 'timestamp' => $t + $qd[3]]);
            }
        }

        return $quotes;

    }

    public function buildRawQuote($pq) {
        $t = $this->base_timestamp;
        $s = function($v) { return $v * 100000000; };
        $q = function($vars) { return $this->newRawQuoteModel($vars); };

        return $q(['bid' => $s($pq[0]), 'last' => $s($pq[1]), 'ask' => $s($pq[2]), 'timestamp' => $t + $pq[3]]);
    }

    // creates a quote
    //   directly in the repository (no validation)
    public function newSampleQuote($quote_vars=[]) {
        $attributes = array_replace($this->sampleQuoteVars(), $quote_vars);
        $quote_model = $this->quote_repository->create($attributes);
        return $quote_model;
    }

    public function newRawQuoteModel($quote_vars=[]) {
        $attributes = array_replace($this->sampleQuoteVars(), $quote_vars);
        return new RawQuote($attributes);
    }

    public function newSampleAggregateQuote($quote_vars=[]) {
        $attributes = array_replace($this->sampleAggregateQuoteVars(), $quote_vars);
        $quote_model = $this->aggregate_quote_repository->create($attributes);
        return $quote_model;
    }

    public function newAggregateQuoteModel($quote_vars=[]) {
        $attributes = array_replace($this->sampleAggregateQuoteVars(), $quote_vars);
        return new AggregateQuote($attributes);
    }


    public function populateDatabaseWithQuotesArray($quotes_array) {
        $raw_quote_repository = $this->app->make('Quotebot\Repositories\RawQuoteRepository');
        $aggregate_quote_repository = $this->app->make('Quotebot\Repositories\AggregateQuoteRepository');

        $quotes = $this->buildQuotesCollectionFromQuotesArray($quotes_array);
        foreach($quotes as $quote) {
            if ($quote instanceof RawQuote) { $raw_quote_repository->saveModel($quote); }
            else if ($quote instanceof AggregateQuote) { $aggregate_quote_repository->saveModel($quote); }
            else { throw new Exception("unknown quote type", 1); }
        }

    }

    public function cleanup() {
        $raw_quote_repository = $this->app->make('Quotebot\Repositories\RawQuoteRepository');
        $aggregate_quote_repository = $this->app->make('Quotebot\Repositories\AggregateQuoteRepository');
        list($name, $pair) = $this->getSampleNameAndPair();
        $future = Carbon::create(2050, 1, 1, 0,0,0)->getTimestamp();

        $raw_quote_repository->deleteByTimestampRange($name, $pair, 0, $future);
        $aggregate_quote_repository->deleteByTimestampRange($name, $pair, 0, $future);
    }



}
