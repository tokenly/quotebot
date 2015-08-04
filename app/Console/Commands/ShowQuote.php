<?php namespace Quotebot\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tokenly\CryptoQuoteClient\Client;
use Tokenly\CurrencyLib\CurrencyUtil;

class ShowQuote extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'quotebot:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads a raw quote from the provider and displays it.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $driver = $this->input->getArgument('driver');
        $pairs = $this->input->getArgument('pairs');

        // convert to ['base' => $base, 'target' => $target]
        $currency_pairs = [];
        foreach($pairs as $pair) {
            list($base, $target) = explode(':', $pair);
            $currency_pairs[] = compact('base', 'target');
        }


        $quote_client = app('Tokenly\CryptoQuoteClient\Client');
        if (count($currency_pairs) == 1) {
            extract($currency_pairs[0]);
            $this->comment("Loading $base:$target from $driver");
            $quote = $quote_client->getQuote($driver, $base, $target);
            $quotes = [$quote];

        } else {
            $this->comment("Loading ".json_encode($pairs, 192)." from $driver");
            $quotes = $quote_client->getQuotes($driver, $currency_pairs);
        }

        foreach($quotes as $quote) {
            $base = $quote['base'];
            $this->line("################################################################################################\n");
            $this->line("Source   : ".$quote['name']);
            $this->line("Currency : ".$quote['target'].' in '.$quote['base']);
            $this->line("Timestamp: ".date("Y-m-d H:i:s", $quote['timestamp']));
            $this->line('');
            $this->line("Ask      : ".CurrencyUtil::satoshisToFormattedString($quote['askSat'], $base == 'USD' ? 2 : 8)." $base");
            $this->line("Bid      : ".CurrencyUtil::satoshisToFormattedString($quote['bidSat'], $base == 'USD' ? 2 : 8)." $base");
            $this->line("Last     : ".CurrencyUtil::satoshisToFormattedString($quote['lastSat'], $base == 'USD' ? 2 : 8)." $base");
            $this->line("\n################################################################################################");
        }


        $this->comment("done");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['driver', InputArgument::OPTIONAL, 'The driver name.', 'bitcoinAverage'],
            ['pairs', InputArgument::IS_ARRAY, 'Currency pairs.', ['USD:BTC']],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }

}
