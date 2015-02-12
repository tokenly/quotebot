<?php namespace Quotebot\Events;

use Quotebot\Events\Event;
use Quotebot\Models\RawQuote;

class QuoteWasLoaded extends Event {

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RawQuote $raw_quote)
    {
        $this->raw_quote = $raw_quote;
    }

}
