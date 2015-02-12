<?php

namespace Quotebot\Quote;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;
use Quotebot\Quote\Aggregator;
use Quotebot\Repositories\AggregateQuoteRepository;
use Quotebot\Repositories\RawQuoteRepository;

/**
* 
*/
class DatabaseAggregator
{
    
    function __construct(RawQuoteRepository $raw_quote_repository, AggregateQuoteRepository $aggregate_quote_repository, Aggregator $aggregator)
    {
        $this->raw_quote_repository       = $raw_quote_repository;
        $this->aggregate_quote_repository = $aggregate_quote_repository;
        $this->aggregator                 = $aggregator;

    }

    public function clearAggregateQuotesForTimeRange($name, $pair, $start_timestamp, $end_timestamp) {
        return $this->aggregate_quote_repository->deleteByTimestampRange($name, $pair, $slice_start_timestamp, $slice_end_timestamp);
    }

    public function aggregateQuotesForTimeRange($name, $pair, $start_timestamp, $end_timestamp, $chunk_modification='+1 hour') {
        return DB::transaction(function() use ($name, $pair, $start_timestamp, $end_timestamp, $chunk_modification) {

            // get the previous quote
            $previous_quote = $this->raw_quote_repository->findOldestQuoteBeforeTimestamp($name, $pair, $start_timestamp);

            // get all quotes in the timestamp range
            $raw_quotes = array_values($this->raw_quote_repository->findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp)->all());

            // loop through the timestamps
            $working_quotes = $raw_quotes;
            $working_time = new Carbon();
            $working_time->setTimestamp($start_timestamp);
            while ($working_time->getTimestamp() < $end_timestamp) {

                $slice_start_timestamp = $working_time->getTimestamp();
                $working_time->modify($chunk_modification);
                $slice_end_timestamp = $working_time->getTimestamp();

                // is there already an aggregate quote for this time range?
                $existing_aggregate_quote = $this->aggregate_quote_repository->findByTimestampRange($name, $pair, $slice_start_timestamp, $slice_end_timestamp)->first();
                if ($existing_aggregate_quote) { continue; }

                // slice the quotes
                $raw_quotes_in_range = $this->getRawQuotesInRange($raw_quotes, $slice_start_timestamp, $slice_end_timestamp);
                // echo "\$raw_quotes_in_range:\n".json_encode($raw_quotes_in_range, 192)."\n";

                // build an aggregate quote
                $aggregate_quote = $this->aggregator->aggregateQuotes($raw_quotes_in_range, $slice_start_timestamp, $slice_end_timestamp, $previous_quote);

                // insert the aggregate quote into the database
                // echo "\$aggregate_quote:\n".json_encode($aggregate_quote, 192)."\n";
                $this->aggregate_quote_repository->create($aggregate_quote->toArray());

                // save the previous quote
                if ($raw_quotes_in_range) {
                    // print "\$raw_quotes_in_range count=".count($raw_quotes_in_range)."\n";
                    // print "isset(\$raw_quotes_in_range[".(count($raw_quotes_in_range) - 1)."]) =".isset($raw_quotes_in_range[count($raw_quotes_in_range) - 1])."\n";
                    $previous_quote = $raw_quotes_in_range[count($raw_quotes_in_range) - 1];
                }
            }

            return;
        });




    }

    protected function getPreviousQuoteForTimestamp($name, $pair, $start_timestamp) {
        // get the last raw quote before the timestamp
        $previous_quote = $this->raw_quote_repository->findOldestQuoteBeforeTimestamp($name, $pair, $start_timestamp);
        return $previous_quote;
    }

    protected function getRawQuotesInRange($raw_quotes, $start_timestamp, $end_timestamp) {
        $out = [];
        foreach($raw_quotes as $raw_quote) {
            $raw_quote_timestamp = $raw_quote['timestamp']->getTimestamp();
            if ($raw_quote_timestamp >= $start_timestamp AND $raw_quote_timestamp <= $end_timestamp) {
                $out[] = $raw_quote;
            }
        }
        return $out;
    }

}