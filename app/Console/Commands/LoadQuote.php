<?php namespace Quotebot\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Quotebot\Commands\LoadQuote as LoadQuoteCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LoadQuote extends Command {

    use DispatchesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'quotebot:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads a raw quote and stores it in the database.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $driver = $this->input->getArgument('driver');
        $base = $this->input->getArgument('base');
        $target = $this->input->getArgument('target');

        $this->comment("Loading $base:$target from $driver");

        $this->dispatch(new LoadQuoteCommand($driver, $base, $target));

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
            ['base', InputArgument::OPTIONAL, 'The base currency.', 'USD'],
            ['target', InputArgument::OPTIONAL, 'The target currency.', 'BTC'],
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
