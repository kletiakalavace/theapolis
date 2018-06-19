<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Carbon\Carbon;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * Archive a job when the publication end date is reached and notify the teammembers or job owner on the dashboard.
 * Class JobArchiveCommand
 * @package Theaterjobs\InserateBundle\Command\CronJob
 */
class JobArchiveCommand extends ContainerAwareCommand
{
    use ContainerTrait, StatisticsTrait, ScheduleUpdateESIndexTrait;

    /** @var EntityManager */
    private $em;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:archive:published-jobs')
            ->setDescription('Archives jobs that are a certain timespan old')
            ->addArgument('jobIds', InputArgument::REQUIRED, 'Job Ids to process');
    }
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = 1;
        $batchSize = 25;
        $this->em = $this->get('doctrine.orm.entity_manager');
        $jobIds = json_decode($input->getArgument('jobIds'));
        $jobs = $this->em->getRepository(Job::class)->findById($jobIds);

        /** @var Job $job */
        foreach ($jobs as $job) {
            $job->setArchivedAt(Carbon::now())
                ->setEmploymentDate(Carbon::now()->addDays(10))
                ->setEmploymentStatus(Job::EMPLOYMENT_STATUS_AWAITING_ANSWER)
                ->setStatus(Job::EMPLOYMENT_STATUS_FAILED);
            $this->sendNotifications($output, $job);
            $this->archivedJobViewsManager($job);

            if ($i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear(Job::class);
                $this->em->clear(Notification::class);
                $this->em->clear(View::class);
            }
            $i++;
        }
        $this->em->flush();
    }
    /**
     * Send notification to users
     *
     * @param $output OutputInterface
     * @param $job Job
     */
    public function sendNotifications($output, $job)
    {
        $organization = $job->getOrganization();
        if($organization){
            $users = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')->findAllUsers($organization->getId(), $this->em);
            if ($users) {
                $notification = new Notification();
                $title = 'dashboard.notification.job.archived.toMembers %title% %organization%';
                $transParams = array(
                    '%title%'        => $job->getTitle(),
                    '%organization%'    => $organization->getName()
                );
                $link = 'tj_inserate_job_route_show';
                $linkParams = array('slug' => $job->getSlug());
                $notification
                    ->setTitle($title)
                    ->setTranslationKeys($transParams)
                    ->setDescription('')
                    ->setRequireAction(false)
                    ->setLinkKeys($linkParams)
                    ->setLink($link);

                $event = (new NotificationEvent())
                    ->setObjectClass(Job::class)
                    ->setObjectId($job->getId())
                    ->setNotification($notification)
                    ->setUsers($users)
                    ->setType('job_archived')
                    ->setFlush(false);

                $this->get('event_dispatcher')->dispatch('notification', $event);
                $output->writeln(sprintf("Sent to users of job '%s' with id '%d'", $job->getTitle(), $job->getId()));
            }
        } else {
            //Send notification to user
            $title = 'dashboard.notification.job.archived %title%';
            $description = 'dashboard.notification.job.archived.description %title%';
            $transParams = ['%title%' => $job->getTitle()];
            $link = 'tj_inserate_job_route_show';
            $linkParams = ['slug' => $job->getSlug()];
            $notification = new Notification();
            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription($description)
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $notificationEvent = (new NotificationEvent())
                ->setObjectClass(Job::class)
                ->setObjectId($job->getId())
                ->setNotification($notification)
                ->setFrom($job->getUser())
                ->setUsers($job->getUser())
                ->setType('job_archived')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
            $output->writeln(sprintf("Sent to user of job  :%s with id : %d", $job->getTitle(), $job->getId()));
        }
    }

    /**
     * Count total views for certain job.
     *
     * @param Job $job
     */
    public function archivedJobViewsManager(Job $job){

        $this->em = $this->get('doctrine.orm.entity_manager');
        $statViews =  $this->countAllViews(Job::class, $job->getId());
        $job->setArchivedViews($statViews + $job->getTotalViews())
            ->setTotalViews(0);

        $ids = $this->em->getRepository(View::class)->deleteViewsIds(Job::class, $job->getId());
        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, View::class, $ids, 'cron');
    }
}