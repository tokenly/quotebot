<?php

namespace Quotebot\Models;

use Carbon\Carbon;
use Quotebot\Models\BaseModel;
use Quotebot\Repositories\Helper\DateHelper;
use Tokenly\CurrencyLib\CurrencyUtil;

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

    public function getSlug() {
        return $this['name'].'_'.str_replace(':', '_', $this['pair']);
    }

    public function toJSONSerializable($force_satoshis = false) {
        if ($force_satoshis) {
            $is_satoshis = true;
        } else {
            $is_usd = in_array(substr($this['pair'], 0, strpos($this['pair'], ':')), ['USD','EUR']);
            $is_satoshis = !$is_usd;
        }

        return [
            'source'     => $this['name'],
            'pair'       => $this['pair'],
            'inSatoshis' => $is_satoshis,
            'bidLow'     => $is_satoshis ? round($this['bid_low'])   : CurrencyUtil::satoshisToValue($this['bid_low'], 2),
            'bidHigh'    => $is_satoshis ? round($this['bid_high'])  : CurrencyUtil::satoshisToValue($this['bid_high'], 2),
            'bidAvg'     => $is_satoshis ? round($this['bid_avg'])   : CurrencyUtil::satoshisToValue($this['bid_avg'], 2),
            'lastLow'    => $is_satoshis ? round($this['last_low'])  : CurrencyUtil::satoshisToValue($this['last_low'], 2),
            'lastHigh'   => $is_satoshis ? round($this['last_high']) : CurrencyUtil::satoshisToValue($this['last_high'], 2),
            'lastAvg'    => $is_satoshis ? round($this['last_avg'])  : CurrencyUtil::satoshisToValue($this['last_avg'], 2),
            'askLow'     => $is_satoshis ? round($this['ask_low'])   : CurrencyUtil::satoshisToValue($this['ask_low'], 2),
            'askHigh'    => $is_satoshis ? round($this['ask_high'])  : CurrencyUtil::satoshisToValue($this['ask_high'], 2),
            'askAvg'     => $is_satoshis ? round($this['ask_avg'])   : CurrencyUtil::satoshisToValue($this['ask_avg'], 2),
            'start'      => Carbon::createFromTimestamp($this->attributes['start_timestamp'])->toIso8601String(),
            'end'        => Carbon::createFromTimestamp($this->attributes['end_timestamp'])->toIso8601String(),
        ];
    }

}
