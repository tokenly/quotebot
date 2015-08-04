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
        $pairs = $this->input->getArgument('pairs');

        $this->comment("Loading ".json_encode($pairs, 192)." from $driver");

        $this->dispatch(new LoadQuoteCommand($driver, $pairs));

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
