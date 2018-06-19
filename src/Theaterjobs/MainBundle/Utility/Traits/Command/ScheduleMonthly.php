<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;

use Carbon\Carbon;
use Theaterjobs\MainBundle\Utility\Traits\Command\CronQueueTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\IsMidnightTrait;

/**
 * Trait ScheduleMonthly
 */
trait ScheduleMonthly
{
    use CronQueueTrait, IsMidnightTrait;

    /**
     * @param \DateTime $lastRunAt
     * @return bool|int
     */
    public function shouldBeScheduled(\DateTime $lastRunAt)
    {
        $lastRun = Carbon::createFromTimestamp($lastRunAt->getTimestamp());
        $now = Carbon::now();
        $firstDay = Carbon::now()->firstOfMonth();

        //If now first day of month and last time ran not today
        $firstDayOfMonth = !$firstDay->diffInDays($now) ? $lastRun->diffInDays($firstDay) : false;
        if ($firstDayOfMonth) {
            return $this->isMidnight($lastRunAt->getTimestamp());
        }
        return false;
    }
}