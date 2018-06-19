<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;

/**
 * Archive a job when the publication end date is reached and notify the teammembers or job owner on the dashboard.
 * Class JobStatisticsCommand
 * @package Theaterjobs\InserateBundle\Command\CronJob
 */
class JobStatisticsCommand extends ContainerAwareCommand
{
    use StatisticsTrait, ScheduleUpdateESIndexTrait, ContainerTrait;

    /** @var EntityManager */
    private $em;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:job:views:statistics')
            ->addArgument('jobIds', InputArgument::REQUIRED, 'Job ids to process')
            ->setDescription('Deletes the views prior to the last 10 days before the cron runs.')   ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get Ids from param
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $ids = $input->getArgument('jobIds');
        $jobIds = json_decode($ids);
        $jobs = $this->em->getRepository(Job::class)->findById($jobIds);

        $i = 1;
        $batchSize = 100;
        $today = Carbon::today()->subDays(10);

        foreach ($jobs as $job) {
            $this->removeViewRecordsPriorThan($job, $today);
            if ($i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear(Job::class);
            }
        }

        $this->em->flush();
        $output->writeln(sprintf("Updated %d jobs", $i));
    }

    /**
     * Set total views on job and schedule elasticsearch bulk index for deleted views
     * @param Job $job
     * @param $date
     */
    public function removeViewRecordsPriorThan(Job $job, $date)
    {
        $statViews = $this->countAllViewsSince(Job::class, $job->getId(), $date);
        $job->setTotalViews($statViews + $job->getTotalViews());

        $ids = $this->em->getRepository(View::class)->deleteViewsPriorThanIds(Job::class, $job->getId(), $date);
        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, View::class, $ids, 'cron');
    }
}
