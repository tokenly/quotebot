<?php

namespace Quotebot\Models;

use Carbon\Carbon;
use Quotebot\Models\BaseModel;
use Quotebot\Repositories\Helper\DateHelper;

class RawQuote extends BaseModel {

    // no automatic dates
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'raw_quotes';


    public function setTimestampAttribute($timestamp) { $this->attributes['timestamp'] = DateHelper::toTimestamp($timestamp); }
    public function getTimestampAttribute() { return Carbon::createFromTimestamp($this->attributes['timestamp']); }

    // public function getTimestamp() {
    //     return $this['timestamp'];
    // }

}
