<?php

namespace Quotebot\Repositories;

use Illuminate\Database\Eloquent\Model;
use Quotebot\Models\RawQuote;
use Quotebot\Repositories\Base\Repository;
use Quotebot\Repositories\Helper\DateHelper;
use \Exception;

/*
* APIRepository
*/
class RawQuoteRepository extends Repository
{

    // must define this
    protected $model_type = 'Quotebot\Models\RawQuote';

    public function findByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        return 
            RawQuote::where('name', $name)->where('pair', $pair)
            ->where('timestamp', '>=', DateHelper::toTimestamp($start_timestamp))
            ->where('timestamp', '<=', DateHelper::toTimestamp($end_timestamp))
            ->orderBy('timestamp', 'asc')
            ->get();
    }

    public function findOldestQuoteBeforeTimestamp($name, $pair, $start_timestamp) {
        return 
            RawQuote::where('name', $name)->where('pair', $pair)
            ->where('timestamp', '<', DateHelper::toTimestamp($start_timestamp))
            ->orderBy('timestamp', 'desc')
            ->limit(1)
            ->first();
    }

    public function deleteByTimestampRange($name, $pair, $start_timestamp, $end_timestamp) {
        return RawQuote::where('name', $name)->where('pair', $pair)
            ->where('timestamp', '>=', DateHelper::toTimestamp($start_timestamp))
            ->where('timestamp', '<=', DateHelper::toTimestamp($end_timestamp))
            ->delete();
    }


    protected function modifyAttributesBeforeCreate($attributes) {
        $out = $attributes;

        $out['timestamp'] = DateHelper::toTimestamp($attributes['timestamp']);

        return $out;
    }


}
