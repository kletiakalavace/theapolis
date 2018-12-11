<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\MainBundle\Utility\CommandSchedulerInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\CommandExecuteTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;

/**
 * Archive a job when the publication end date is reached and notify the teammembers or job owner on the dashboard.
 * Class JobStatisticsScheduleCommand
 * @package Theaterjobs\InserateBundle\Command\CronJob
 */
class JobStatisticsScheduleCommand extends ContainerAwareCommand implements CronCommand, CommandSchedulerInterface
{
    use ScheduleEveryNight, ContainerTrait, StatisticsTrait, CommandExecuteTrait;

    const PROCESSING_JOB = "theaterjobs:cron:job:views:statistics";
    const CHUNK_SIZE = 100;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:schedule:job:views:statistics')
            ->setDescription('Scheduler to delete the views prior to the last 10 days before the cron runs.');
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        return $em->getRepository(Job::class)->jobsForViewStatsCleanupIds();
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return self::PROCESSING_JOB;
    }

    /**
     * @return integer
     */
    public function getChunkSize()
    {
        return self::CHUNK_SIZE;
    }
}
