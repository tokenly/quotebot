<?php

namespace Quotebot\Handlers\Events;

use Illuminate\Support\Facades\Log;
use Quotebot\Events\Event;
use Tokenly\PusherClient\Client;


class QuoteWasLoadedHandler {

    function __construct(Client $pusher_client) {
        $this->pusher_client = $pusher_client;
    }


    public function sendQuoteEventToPusher(Event $event) {
        $raw_quote = $event->raw_quote;

        $channel = '/quotebot_quote_'.$raw_quote['name'].'_'.str_replace(':', '_', $raw_quote['pair']);
        Log::debug("sending quote to channel $channel");
        $data = $raw_quote->toJSONSerializable();
        $data['last'] = $data['last'] + rand(20,100);
        $this->pusher_client->send($channel, $data);
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
