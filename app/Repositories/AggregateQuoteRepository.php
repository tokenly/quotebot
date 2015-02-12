<?php

namespace Quotebot\Repositories;

use Illuminate\Database\Eloquent\Model;
use Quotebot\Models\AggregateQuote;
use Quotebot\Repositories\Base\Repository;
use Quotebot\Repositories\Helper\DateHelper;
use \Exception;

/*
* APIRepository
*/
class AggregateQuoteRepository extends Repository
{

    // must define this
    protected $model_type = 'Quotebot\Models\AggregateQuote';


    public function findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        return 
            AggregateQuote::where('name', $name)->where('pair', $pair)
            ->where('start_timestamp', '>=', DateHelper::toTimestamp($start_timestamp))
            ->where('start_timestamp', '<=', DateHelper::toTimestamp($end_timestamp))
            ->orderBy('start_timestamp', 'asc')
            ->get();
    }

    public function deleteByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        return AggregateQuote::where('name', $name)->where('pair', $pair)
            ->where('start_timestamp', '>=', DateHelper::toTimestamp($start_timestamp))
            ->where('start_timestamp', '<=', DateHelper::toTimestamp($end_timestamp))
            ->delete();
    }

    protected function modifyAttributesBeforeCreate($attributes) {
        $out = $attributes;

        $out['start_timestamp'] = DateHelper::toTimestamp($attributes['start_timestamp']);
        $out['end_timestamp'] = DateHelper::toTimestamp($attributes['end_timestamp']);

        return $out;
    }

}
