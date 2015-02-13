<?php

namespace Quotebot\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Quotebot\Http\Controllers\Controller;
use Quotebot\Http\Requests;
use Quotebot\Quote\Registry;
use Quotebot\Quote\Selector;
use Quotebot\Repositories\RawQuoteRepository;

class QuoteController extends Controller {

    public function __construct() {
        // catch all errors and return a JSON response
        $this->middleware('api.catchErrors');

        // require hmacauth middleware for all API requests by default
        $this->middleware('api.publicAuth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function get()
    {
        
    }

    public function index(Registry $quote_registry, Selector $selector, RawQuoteRepository $raw_quote_repository)
    {
        $end_timestamp = Carbon::create();
        $start_timestamp = $end_timestamp->copy()->modify('-24 hours');

        $out = ['quotes' => []];
        foreach ($quote_registry->allQuoteTypesIterator() as $name => $pair) {
            $aggregate_quote = $selector->buildAggregateQuoteByTimestampRange($name, $pair, $start_timestamp, $end_timestamp);
            $out['quotes'][$name] = array_merge(
                ['source' => '', 'pair' => '', 'inSatoshis' => null, 'bid' => 0, 'last' => 0, 'ask' => 0], 
                ($aggregate_quote ? $aggregate_quote->toJSONSerializable() : [])
            );

            // get the last raw quote
            $latest_quote = $raw_quote_repository->findOldestQuote($name, $pair);
            $out['quotes'][$name] = array_merge($out['quotes'][$name], $latest_quote ? $latest_quote->toJSONSerializable() : []);
        }


        $out['start'] = $start_timestamp->toIso8601String();
        $out['end'] = $end_timestamp->toIso8601String();

        return $out;
    }


}
