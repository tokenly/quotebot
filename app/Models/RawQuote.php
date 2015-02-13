<?php

namespace Quotebot\Models;

use Carbon\Carbon;
use Quotebot\Models\BaseModel;
use Quotebot\Repositories\Helper\DateHelper;
use Tokenly\CurrencyLib\CurrencyUtil;

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

    public function toJSONSerializable($force_satoshis = false) {
        if ($force_satoshis) {
            $is_satoshis = true;
        } else {
            $is_usd = substr($this['pair'], 0, strpos($this['pair'], ':')) == 'USD';
            $is_satoshis = !$is_usd;
        }

        return [
            'source'     => $this['name'],
            'pair'       => $this['pair'],
            'inSatoshis' => $is_satoshis,
            'bid'        => $is_satoshis ? round($this['bid'])  : CurrencyUtil::satoshisToValue($this['bid'], 2),
            'last'       => $is_satoshis ? round($this['last']) : CurrencyUtil::satoshisToValue($this['last'], 2),
            'ask'        => $is_satoshis ? round($this['ask'])  : CurrencyUtil::satoshisToValue($this['ask'], 2),
            'time'       => Carbon::createFromTimestamp($this->attributes['timestamp'])->toIso8601String(),
        ];
    }

}
