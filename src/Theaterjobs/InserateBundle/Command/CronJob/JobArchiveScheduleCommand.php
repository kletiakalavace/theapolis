<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\InserateBundle\Entity\Job;
use Carbon\Carbon;
use Theaterjobs\MainBundle\Utility\CommandSchedulerInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\CommandExecuteTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;

/**
 * Load jobs in queue to archive published jobs
 * Class JobArchiveScheduleCommand
 * @package Theaterjobs\InserateBundle\Command\CronJob
 */
class JobArchiveScheduleCommand extends ContainerAwareCommand implements CronCommand, CommandSchedulerInterface
{
    use ScheduleEveryNight, ContainerTrait, CommandExecuteTrait;

    const CHUNK_SIZE = 100;
    const PROCESSING_JOB = "theaterjobs:cron:archive:published-jobs";

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:schedule:archive:published-jobs')
            ->setDescription('Archives jobs that are a certain timespan old');
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $esm = $this->get('fos_elastica.manager');
        $query = $esm->getRepository(Job::class)->expiredPublishedJobs(Carbon::today()->format('Y-m-d'));
        $result = $this->get('fos_elastica.index.theaterjobs.job')->search($query, 20000);
        return $this->concatIds($result->getResults());
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

    /**
     * @param $results
     * @return mixed
     */
    private function concatIds($results)
    {
        return array_reduce($results, function ($acc, $item) {
            $acc[] = $item->id;
            return $acc;
        }, []);
    }
}
