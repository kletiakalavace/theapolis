<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Entity\Notification;
use Carbon\Carbon;

/**
 * Send notification to expiring jobs
 *
 * @author Jurgen Rexhmati <rexhmatjurgen@gmail.com>
 */
class JobPublicationEndingNotificationCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /** @var EntityManager */
    private $em;

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:job:notify-publication-end')
            ->setDescription('Notify job owner when a job is 5 days away from publication end');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getEm();
        $endDate = Carbon::now()->addDays(5);
        $esm = $this->get('fos_elastica.manager');
        $query = $esm->getRepository(Job::class)->expiredPublishedJobs($endDate->format('Y-m-d'), true);
        $jobs = $this->get('fos_elastica.index.theaterjobs.job')->search($query, 20000);
        $this->sendNotifications($jobs, $output);
    }

    /**
     * Send all notifications to users
     *
     * @param array $jobs
     * @param OutputInterface $output output interface
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function sendNotifications($jobs, OutputInterface $output)
    {
        foreach ($jobs as $job) {
            $organization = $job->organization;
            $notification = new Notification();

            if ($organization) {
                $users = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')->findAllUsers($organization['id'], $this->em);
                if ($users) {
                    $title = 'dashboard.notification.job.publicationEnd.toMembers %title% %date% %organization%';
                    // @todo implement locale support for carbon date
                    $format = new Carbon($job->publicationEnd);
                    $date = $format->format('d. M Y H:m');

                    $transParams = [
                        '%title%' => $job->title,
                        '%date%' => $date,
                        '%organization%' => $organization['name']
                    ];

                    $link = 'tj_inserate_job_route_show';
                    $linkParams = ['slug' => $job->slug];
                } else {
                    continue;
                }
            } else {
                $title = 'dashboard.notification.job.publicationEnd %title%';
                $transParams = ['%title%' => $job->title];

                $link = "tj_inserate_job_route_show";
                $linkParams = ['slug' => $job->slug];
                $users = $this->em->find(User::class, $job->user['id']);
            }

            $notification
                ->setLink($link)
                ->setTitle($title)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLinkKeys($linkParams)
                ->setTranslationKeys($transParams);

            $event = (new NotificationEvent())
                ->setUsers($users)
                ->setFlush(false)
                ->setObjectId($job->id)
                ->setObjectClass(Job::class)
                ->setNotification($notification)
                ->setType('job_runs_out_of_date');

            $s = $organization ? 's' : '';
            $this->get('event_dispatcher')->dispatch('notification', $event);
            $output->writeln(sprintf("Sent to user$s of job : %s with id %d", $job->title, $job->id));
        }
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->get('doctrine')->getEntityManager();
    }
}
