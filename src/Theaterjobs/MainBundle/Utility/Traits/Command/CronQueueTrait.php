<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;

use Symfony\Component\Console\Command\Command;
use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Trait CronQueueTrait
 * @package Theaterjobs\MainBundle\Utility\Traits
 */
trait CronQueueTrait {

    public function createCronJob(\DateTime $lastRunAt)
    {
        if ( ! $this instanceof Command) {
            throw new \LogicException('This trait must be used in Symfony console commands only.');
        }

        $job = new JobQueue($this::getName(), [], true, "cron", JobQueue::PRIORITY_HIGH);
        return $job;
    }
}