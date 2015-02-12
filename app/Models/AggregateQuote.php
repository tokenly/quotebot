<?php

namespace Quotebot\Models;

use Carbon\Carbon;
use Quotebot\Models\BaseModel;
use Quotebot\Repositories\Helper\DateHelper;

class AggregateQuote extends BaseModel {

    // no automatic dates
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'aggregate_quotes';

    // public function getTimestamp() {
    //     return $this['start_timestamp'];
    // }


    public function setStartTimestampAttribute($start_timestamp) { $this->attributes['start_timestamp'] = DateHelper::toTimestamp($start_timestamp); }
    public function getStartTimestampAttribute() { return Carbon::createFromTimestamp($this->attributes['start_timestamp']); }

    public function setEndTimestampAttribute($end_timestamp) { $this->attributes['end_timestamp'] = DateHelper::toTimestamp($end_timestamp); }
    public function getEndTimestampAttribute() { return Carbon::createFromTimestamp($this->attributes['end_timestamp']); }


}
