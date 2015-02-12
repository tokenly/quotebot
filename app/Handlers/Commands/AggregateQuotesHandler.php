<?php namespace Quotebot\Handlers\Commands;

use Quotebot\Commands\AggregateQuotes;
use Quotebot\Quote\DatabaseAggregator;
use Quotebot\Repositories\Helper\DateHelper;

class AggregateQuotesHandler {

	/**
	 * Create the command handler.
	 *
	 * @return void
	 */
	public function __construct(DatabaseAggregator $db_aggregator)
	{
		$this->db_aggregator = $db_aggregator;
	}

	/**
	 * Handle the command.
	 *
	 * @param  AggregateQuotes  $command
	 * @return void
	 */
	public function handle(AggregateQuotes $command)
	{
		$this->db_aggregator->aggregateQuotesForTimeRange($command->name, $command->base.":".$command->target, DateHelper::toTimestamp($command->start), DateHelper::toTimestamp($command->end));
	}

}
