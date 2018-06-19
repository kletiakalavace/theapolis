<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;

use Carbon\Carbon;

/**
 * Check if its midnight and last run is not between 00:00-01:00, used in commands
 * Trait IsMidnightTrait
 * @package Theaterjobs\MainBundle\Utility\Traits
 */
trait IsMidnightTrait
{

    /**
     * @param $lastRunAt int timestamp
     * @return bool
     */
    public function isMidnight($lastRunAt)
    {
        $start = Carbon::createFromTime('0')->getTimestamp();
        $end = Carbon::createFromTime('1')->getTimestamp();
        $now = Carbon::now()->getTimestamp();
        $gap = $now - $lastRunAt;
        // Now is between [12:00am-01:00am]
        // And Time between LastRunTime and now is bigger than one hour
        return $start < $now && $now < $end && $gap > 60 * 60;
    }

}