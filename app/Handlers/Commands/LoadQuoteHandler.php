<?php namespace Quotebot\Handlers\Commands;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Quotebot\Commands\LoadQuote;
use Quotebot\Events\QuoteWasLoaded;
use Quotebot\Repositories\RawQuoteRepository;
use Tokenly\CryptoQuoteClient\Client;
use Tokenly\LaravelEventLog\Facade\EventLog;

class LoadQuoteHandler {

    /**
     * Create the command handler.
     *
     * @return void
     */
    public function __construct(Client $quote_client, RawQuoteRepository $raw_quote_repository, Dispatcher $events)
    {
        $this->quote_client         = $quote_client;
        $this->raw_quote_repository = $raw_quote_repository;
        $this->events               = $events;
    }

    /**
     * Handle the command.
     *
     * @param  LoadQuote  $command
     * @return void
     */
    public function handle(LoadQuote $command)
    {
        try {
            // get quote
            $quote = $this->quote_client->getQuote($command->driver, $command->base, $command->target);

            // store in DB
            $create_vars = [
                'name'      => $quote['name'],
                'pair'      => $quote['base'].':'.$quote['target'],
                'ask'       => $quote['askSat'],
                'bid'       => $quote['bidSat'],
                'last'      => $quote['lastSat'],
                'timestamp' => $quote['timestamp'],
            ];
            $raw_quote = $this->raw_quote_repository->create($create_vars);

            // log event
            EventLog::log('quote.loaded', $create_vars);

            // fire event
            $this->events->fire(new QuoteWasLoaded($raw_quote));

            EventLog::log('quote.loaded', $create_vars);

        } catch (Exception $e) {
            EventLog::logError('loadquote.failed', $e, ['command' => (array)$command]);
            throw $e;
        }
    }

}
