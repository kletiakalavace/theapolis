<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;

/**
 * Trait ScheduleEveryNight
 * @package Theaterjobs\MainBundle\Utility\Traits
 */
trait ScheduleEveryNight
{
    use CronQueueTrait, IsMidnightTrait;

    /**
     * @param \DateTime $lastRunAt
     * @return bool
     */
    public function shouldBeScheduled(\DateTime $lastRunAt)
    {
        return $this->isMidnight($lastRunAt->getTimestamp());
    }
}