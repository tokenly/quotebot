<?php

namespace Quotebot\Quote;

use Carbon\Carbon;
use Quotebot\Models\AggregateQuote;
use Quotebot\Models\RawQuote;

/**
* 
*/
class Aggregator
{
    
    function __construct()
    {
    }

    // returns a high, low, avg for the time period
    // low and and is inclusive for the start and end timestamp
    // avg is weighted
    public function aggregateQuotes($quotes, $start_timestamp, $end_timestamp, RawQuote $previous_quote=null) {
        // must have at least 1 quote
        if (!$quotes AND !$previous_quote) { return null; }


        $vars = [
            'name'            => null,
            'pair'            => null,
            'bid_low'         => null,
            'bid_high'        => null,
            'bid_avg'         => null,
            'last_low'        => null,
            'last_high'       => null,
            'last_avg'        => null,
            'ask_low'         => null,
            'ask_high'        => null,
            'ask_avg'         => null,
            'start_timestamp' => null,
            'end_timestamp'   => null,
        ];

        $apply_avg_fn = function($avg, $previous_quote, $previous_timestamp, $timestamp) {
            if ($previous_quote instanceof AggregateQuote) {
                $prev_bid = $previous_quote['bid_avg'];
                $prev_last = $previous_quote['last_avg'];
                $prev_ask = $previous_quote['ask_avg'];

            } else {
                $prev_bid = $previous_quote['bid'];
                $prev_last = $previous_quote['last'];
                $prev_ask = $previous_quote['ask'];
            }

            $previous_timestamp = ($previous_timestamp instanceof Carbon) ? $previous_timestamp->getTimestamp() : $previous_timestamp;
            $timestamp = ($timestamp instanceof Carbon) ? $timestamp->getTimestamp() : $timestamp;

            $weight = $timestamp - $previous_timestamp;
            $avg['bid_avg_count']  += $weight;
            $avg['bid_avg_sum']    += $prev_bid * $weight;
            $avg['last_avg_count'] += $weight;
            $avg['last_avg_sum']   += $prev_last * $weight;
            $avg['ask_avg_count']  += $weight;
            $avg['ask_avg_sum']    += $prev_ask * $weight;

            return $avg;
        };

        // if there are no quotes, but there is a previous quote, then use the previous quote
        if (!$quotes) { $quotes = [$previous_quote]; }


        $first_quote = null;
        $previous_timestamp = $start_timestamp;
        $avg['bid_avg_count']  = 0;
        $avg['bid_avg_sum']    = 0;
        $avg['last_avg_count'] = 0;
        $avg['last_avg_sum']   = 0;
        $avg['ask_avg_count']  = 0;
        $avg['ask_avg_sum']    = 0;
        foreach($quotes as $quote) {
            if ($first_quote === null) { $first_quote = $quote; }
            if ($quote instanceof AggregateQuote) {
                $vars['bid_low']   = (($vars['bid_low'] === null)   ? $quote['bid_low']   : min($vars['bid_low'], $quote['bid_low']));
                $vars['last_low']  = (($vars['last_low'] === null)  ? $quote['last_low']  : min($vars['last_low'], $quote['last_low']));
                $vars['ask_low']   = (($vars['ask_low'] === null)   ? $quote['ask_low']   : min($vars['ask_low'], $quote['ask_low']));

                $vars['bid_high']  = (($vars['bid_high'] === null)  ? $quote['bid_high']  : max($vars['bid_high'], $quote['bid_high']));
                $vars['last_high'] = (($vars['last_high'] === null) ? $quote['last_high'] : max($vars['last_high'], $quote['last_high']));
                $vars['ask_high']  = (($vars['ask_high'] === null)  ? $quote['ask_high']  : max($vars['ask_high'], $quote['ask_high']));

                $quote_timestamp = $quote['start_timestamp']->getTimestamp();
            } else {
                $vars['bid_low']   = (($vars['bid_low'] === null)   ? $quote['bid']       : min($vars['bid_low'], $quote['bid']));
                $vars['last_low']  = (($vars['last_low'] === null)  ? $quote['last']      : min($vars['last_low'], $quote['last']));
                $vars['ask_low']   = (($vars['ask_low'] === null)   ? $quote['ask']       : min($vars['ask_low'], $quote['ask']));

                $vars['bid_high']  = (($vars['bid_high'] === null)  ? $quote['bid']       : max($vars['bid_high'], $quote['bid']));
                $vars['last_high'] = (($vars['last_high'] === null) ? $quote['last']      : max($vars['last_high'], $quote['last']));
                $vars['ask_high']  = (($vars['ask_high'] === null)  ? $quote['ask']       : max($vars['ask_high'], $quote['ask']));

                $quote_timestamp = $quote['timestamp']->getTimestamp();
            }

            // apply averages if there is a previous quote
            if ($previous_quote) {
                $avg = $apply_avg_fn($avg, $previous_quote, $previous_timestamp, $quote_timestamp);
            }

            $previous_timestamp = $quote_timestamp;
            $previous_quote = $quote;
        }

        // apply last avg
        if ($previous_quote) {
            $avg = $apply_avg_fn($avg, $previous_quote, $previous_quote['timestamp'], $end_timestamp);
        }

        // do averages
        $vars['bid_avg'] = ($avg['bid_avg_count'] === 0) ? 0 : ($avg['bid_avg_sum'] / $avg['bid_avg_count']);
        $vars['last_avg'] = ($avg['last_avg_count'] === 0) ? 0 : ($avg['last_avg_sum'] / $avg['last_avg_count']);
        $vars['ask_avg'] = ($avg['ask_avg_count'] === 0) ? 0 : ($avg['ask_avg_sum'] / $avg['ask_avg_count']);

        // add timestamps
        $vars['start_timestamp'] = $start_timestamp;
        $vars['end_timestamp'] = $end_timestamp;

        // set name and pair
        if (!$first_quote) { return null; }
        $vars['name'] = $first_quote['name'];
        $vars['pair'] = $first_quote['pair'];

        return new AggregateQuote($vars);
    }

}