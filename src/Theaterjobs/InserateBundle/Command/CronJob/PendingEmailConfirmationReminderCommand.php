<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;


use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\AdminBundle\Model\JobRequestSearch;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 *
 * Notify users for new published jobs and his saved search applies
 *
 * Class SaveSearchJobNotification
 */
class PendingEmailConfirmationReminderCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /** @var EntityManager */
    private $em;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('theaterjobs:cron:job:pendingEmailConfirmation:reminder')
            ->setDescription('Sends notification to users to remind them for jobs that are pending email confirmation to be published!');
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $jobRequestSearch = new JobRequestSearch();
        $jobRequestSearch->setStatus('email');
        $this->em = $this->get('doctrine.orm.entity_manager');
        $pendingPublications = $this->em->getRepository(Job::class)
            ->getPendingEmailConfirmationJobsForNotify($this->getContainer()->getParameter('remind_job_email_confirmation_days'));

        //Counting this way to avoid another loop.
        $count = 0;
        $usersIds = [];

        //Get all the id's of profiles that will be notified for their respective jobs.
        foreach ($pendingPublications as $key => $pendingPublication) {
            $count++;
            $usersIds[] = $pendingPublication['user'];
        }

        //Select all the profiles.
        $users = $this->em->getRepository('TheaterjobsUserBundle:User')->findBy(['id' => $usersIds]);


        foreach ($pendingPublications as $key => $pendingPublication) {

            $filtered = array_filter($users, function ($user) use ($pendingPublication) {
                return $user->getId() == $pendingPublication['user'];
            });

            $user = $filtered[0];

            //Send notification to user
            $title = 'dashboard.notification.job.pending.confirmation.email';
            $description = 'dashboard.notification.job.pending.confirmation.email.description';
            $link = 'tj_inserate_job_route_show';
            $notification = new Notification();
            $notification->setTitle($title)
                ->setDescription($description)
                ->setCreatedAt(Carbon::now())
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys(['slug' => $pendingPublication['slug']]);

            $event = (new NotificationEvent())
                ->setObjectClass(Profile::class)
                ->setObjectId($user->getProfile()->getId())
                ->setNotification($notification)
                ->setUsers($user)
                ->setType('job_email_confirmation');

            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch('notification', $event);

            $emailContent = $this->get('twig')->render('TheaterjobsInserateBundle:Job/email:pendingEmailConfirmation.html.twig', [
                'user'=> $user,
                'route'=> $link,
                'slug'=> $pendingPublication['slug']
            ]);
            ;

            $this->get('base_mailer')
                ->sendEmailMessage(
                    $this->getContainer()->get('translator')->trans($title, [], 'messages'),
                    $emailContent,
                    $this->getContainer()->getParameter('company_email'),
                    $user->getEmail(),
                    'text/html'
                );

        }

        $output->writeln(sprintf("Notifications were sent for %d job publications that were pending email publication. ", $count));
    }
}