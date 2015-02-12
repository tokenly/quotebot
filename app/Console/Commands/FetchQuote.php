<?php namespace Quotebot\Console\Commands;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Command;
use Quotebot\Quote\Selector;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tokenly\CurrencyLib\CurrencyUtil;

class FetchQuote extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'quotebot:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Select a quote from the database.';


    function __construct(Selector $selector) {
        $this->selector = $selector;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $name = $this->input->getArgument('name');
        $base = $this->input->getArgument('base');
        $target = $this->input->getArgument('target');
        $start = $this->input->getArgument('start');
        $end = $this->input->getArgument('end');

        $this->comment("Loading $base:$target from $name from $start to $end");

        $aggregate_quote = $this->selector->buildAggregateQuoteByTimestampRange($name, "$base:$target", $start->getTimestamp(), $end->getTimestamp());
        // $this->line(json_encode($aggregate_quote, 192));
        $this->line("################################################################################################\n");
        $this->line("Source  : ".$aggregate_quote['name']);
        $this->line("Currency: ".substr($aggregate_quote['pair'], strpos($aggregate_quote['pair'], ':') + 1));
        $this->line("Range   : ".$aggregate_quote['start_timestamp']." to ".$aggregate_quote['end_timestamp']);
        $this->line('');
        $this->line("Low     : ".CurrencyUtil::satoshisToFormattedString($aggregate_quote['last_low'], $base == 'USD' ? 2 : 8)." $base");
        $this->line("Average : ".CurrencyUtil::satoshisToFormattedString($aggregate_quote['last_avg'], $base == 'USD' ? 2 : 8)." $base");
        $this->line("High    : ".CurrencyUtil::satoshisToFormattedString($aggregate_quote['last_high'], $base == 'USD' ? 2 : 8)." $base");
        $this->line("\n################################################################################################");

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
            ['name', InputArgument::OPTIONAL, 'The driver name.', 'bitcoinAverage'],
            ['base', InputArgument::OPTIONAL, 'The base currency.', 'USD'],
            ['target', InputArgument::OPTIONAL, 'The target currency.', 'BTC'],
            ['start', InputArgument::OPTIONAL, 'Date range start.', Carbon::create()->modify('-24 hours')],
            ['end', InputArgument::OPTIONAL, 'Date range end.', Carbon::create()],
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
