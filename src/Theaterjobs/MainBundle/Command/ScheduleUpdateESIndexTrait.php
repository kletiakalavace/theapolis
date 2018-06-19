<?php

namespace Theaterjobs\MainBundle\Command;

use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Trait ScheduleUpdateESIndexTrait
 * @package Theaterjobs\MainBundle\Command
 */
trait ScheduleUpdateESIndexTrait
{
    /**
     * @param $action "delete, update"
     * @param $nameClass
     * @param $ids
     * @param string $queue
     * @param boolean $flush
     * @param null $dql
     * @param int $chunkSize
     */
    protected function scheduleESIndex($action, $nameClass, $ids, $queue = "app", $flush = false, $dql = null, $chunkSize = 200)
    {
        $i = 1;
        $batchSize = 20;
        $chunks = array_chunk($ids, $chunkSize);
        $em = isset($this->em) ? $this->em : $this->get('doctrine.orm.entity_manager');

        foreach ($chunks as $chunk) {
            $args = [$action, $nameClass, json_encode($chunk), $dql];
            $job = new JobQueue("theaterjobs:cron:update:es:index", $args, true, $queue);
            $em->persist($job);

            if ($i % $batchSize === 0) {
                if ($flush) {
                    $em->flush();
                    $em->clear(JobQueue::class);
                }
            }
        }
        if ($flush) {
            $em->flush();
        }
    }
}