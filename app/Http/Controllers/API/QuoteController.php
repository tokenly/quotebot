<?php

namespace Quotebot\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function index(Registry $quote_registry, Selector $selector)
    {
        $out = ['quotes' => []];
        foreach ($quote_registry->allQuoteTypesIterator() as $name => $pair_data) {
            list($pair, $alias) = $pair_data;
            $quote = $selector->getLatestCombinedQuoteAsJSON($name, $pair);
            $out['quotes'][] = $quote;

            if ($alias) {
                $out['quotes'][] = $this->cloneQuoteToAlias($quote, $alias);
            }
        }

        $response = new Response($out, 200, ['Access-Control-Allow-Origin' => '*']);
        return $response;
    }


    protected function cloneQuoteToAlias($quote, $alias) {
        $quote_out = $quote;
        $quote_out['pair'] = $alias;
        return $quote_out;
    }
}
