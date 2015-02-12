<?php

namespace Quotebot\Repositories\Helper;

use Carbon\Carbon;
use Exception;

/*
* DateHelper
*/
class DateHelper {


    public static function toCarbon($timestamp) {
        if ($timestamp instanceof Carbon) { return $timestamp; }

        // assume an int timestamp
        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp($timestamp);
        }

        // assume a formatted string
        if (strlen($timestamp)) {
            return Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $timestamp);
        }

        throw new Exception("Unknwon timestamp format: $timestamp", 1);
        
    }

    public static function toTimestamp($timestamp) {
        if ($timestamp === null) { return null; }
        if (is_numeric($timestamp)) { return $timestamp; }
        return self::toCarbon($timestamp)->getTimestamp();
    }

}
