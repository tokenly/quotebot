<?php

namespace Quotebot\Handlers\Events;

use Illuminate\Support\Facades\Log;
use Quotebot\Events\Event;
use Quotebot\Quote\Selector;
use Tokenly\PusherClient\Client;


class QuoteWasLoadedHandler {

    function __construct(Client $pusher_client, Selector $selector) {
        $this->pusher_client = $pusher_client;
        $this->selector = $selector;
    }


    public function sendQuoteEventToPusher(Event $event) {
        $raw_quote = $event->raw_quote;

        // wipe the latest quote from the cache
        $this->selector->clearLatestCombinedQuote($raw_quote['name'], $raw_quote['pair']);

        // build a new one
        $data = $this->selector->getLatestCombinedQuoteAsJSON($raw_quote['name'], $raw_quote['pair']);

        $channel = '/quotebot_quote_'.$raw_quote->getSlug();
        Log::debug("sending quote to channel $channel");
        $this->pusher_client->send($channel, $data);

        // also send to the all channel
        $this->pusher_client->send('/quotebot_quote_all', $data);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen('Quotebot\Events\QuoteWasLoaded', 'Quotebot\Handlers\Events\QuoteWasLoadedHandler@sendQuoteEventToPusher');
    }


}
