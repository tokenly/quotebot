<?php namespace Quotebot\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Quotebot\Commands\AggregateQuotes as AggregateQuotesCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AggregateQuotes extends Command {

    use DispatchesCommands;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'quotebot:aggregate-quotes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Aggregate quotes.';

    function __construct() {
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

        $this->comment("Aggregating $name $base:$target from $start to $end");

        $this->dispatch(new AggregateQuotesCommand($name, $base, $target, $start, $end));

        $this->comment("done");

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {

    	$start_of_hour = Carbon::create(null,null,null,null,0,0);

        return [
            ['name', InputArgument::OPTIONAL, 'The driver name.', 'bitcoinAverage'],
            ['base', InputArgument::OPTIONAL, 'The base currency.', 'USD'],
            ['target', InputArgument::OPTIONAL, 'The target currency.', 'BTC'],
            ['start', InputArgument::OPTIONAL, 'Date range start.', $start_of_hour->copy()->modify('-1 hour')],
            ['end', InputArgument::OPTIONAL, 'Date range end.', $start_of_hour->copy()],
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
