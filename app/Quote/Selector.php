<?php

namespace Quotebot\Quote;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use Quotebot\Quote\Aggregator;
use Quotebot\Repositories\AggregateQuoteRepository;
use Quotebot\Repositories\Helper\DateHelper;
use Quotebot\Repositories\RawQuoteRepository;

/**
* 
*/
class Selector
{
    
    function __construct(AggregateQuoteRepository $aggregate_quote_repository, RawQuoteRepository $raw_quote_repository, Aggregator $aggregator) {
        $this->aggregate_quote_repository = $aggregate_quote_repository;
        $this->raw_quote_repository       = $raw_quote_repository;
        $this->aggregator                 = $aggregator;
    }

    public function buildCacheKey($name, $pair) {
        return $name.'_'.str_replace(':', '_', $pair);
    }

    public function clearLatestCombinedQuote($name, $pair) {
        Cache::forget($this->buildCacheKey($name, $pair));
    }

    public function getLatestCombinedQuoteAsJSON($name, $pair) {
        $minutes = 1;
        Log::debug("getLatestCombinedQuoteAsJSON: $name,$pair");
        return Cache::remember($this->buildCacheKey($name, $pair), $minutes, function() use ($name, $pair) {
            Log::debug("buildling LIVE quote: $name,$pair");
            return $this->buildCombinedQuoteAsJSON($name, $pair);
        });
    }

    public function buildCombinedQuoteAsJSON($name, $pair) {
        $end_timestamp = Carbon::create();
        $start_timestamp = $end_timestamp->copy()->modify('-24 hours');

        $aggregate_quote = $this->buildAggregateQuoteByTimestampRange($name, $pair, $start_timestamp, $end_timestamp);
        $combined_quote = array_merge(
            ['source' => '', 'pair' => '', 'inSatoshis' => null, 'bid' => 0, 'last' => 0, 'ask' => 0], 
            ($aggregate_quote ? $aggregate_quote->toJSONSerializable() : [])
        );

        // get the last raw quote
        $latest_quote = $this->raw_quote_repository->findOldestQuote($name, $pair);
        $combined_quote = array_merge($combined_quote, $latest_quote ? $latest_quote->toJSONSerializable() : []);

        return $combined_quote;
    }

    public function buildAggregateQuoteByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        $quotes = $this->findQuotesByTimestampRange($name, $pair, $start_timestamp, $end_timestamp);
        $previous_quote = $this->raw_quote_repository->findOldestQuoteBeforeTimestamp($name, $pair, $start_timestamp);
        return $this->aggregator->aggregateQuotes($quotes, $start_timestamp, $end_timestamp, $previous_quote);
    }

    public function getLastQuote() {
        $quotes = $this->raw_quote_repository->findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp)->all();
    }

    // inclusive for the start and end timestamp
    public function findQuotesByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        $start_timestamp = DateHelper::toTimestamp($start_timestamp);
        $end_timestamp = DateHelper::toTimestamp($end_timestamp);

        // start with aggregate quotes
        $aggregate_quotes = $this->aggregate_quote_repository->findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp)->all();

        if ($aggregate_quotes) {
            $aggregate_quotes_start_timestamp = DateHelper::toTimestamp($aggregate_quotes[0]['start_timestamp']);
            $aggregate_quotes_end_timestamp   = DateHelper::toTimestamp($aggregate_quotes[count($aggregate_quotes)-1]['end_timestamp']);
        } else {
            $aggregate_quotes = [];
            $aggregate_quotes_start_timestamp = 0;
            $aggregate_quotes_end_timestamp   = 0;
        }

        // $quotes = $aggregate_quotes;

        $quotes = [];

        if ($aggregate_quotes) {
            // merge raw and aggregate quotes

            // start with raw quotes before aggregate quotes
            if (($aggregate_quotes_start_timestamp - 1) - $start_timestamp >= 0) {
                $quotes = $this->raw_quote_repository->findByTimestampRange($name, $pair, $start_timestamp, $aggregate_quotes_start_timestamp - 1)->all();
            }

            // add all the aggregate quotes
            $quotes = array_merge($quotes, $aggregate_quotes);

            // and end with raw quotes after aggregate quote range
            if ($end_timestamp - ($aggregate_quotes_end_timestamp) >= 0) {
                $raw_quotes = $this->raw_quote_repository->findByTimestampRange($name, $pair, $aggregate_quotes_end_timestamp, $end_timestamp)->all();
                $quotes = array_merge($quotes, $raw_quotes);
            }
        } else {
            // just raw quotes
            $quotes = $this->raw_quote_repository->findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp)->all();

        }

        return $quotes;
    }

}