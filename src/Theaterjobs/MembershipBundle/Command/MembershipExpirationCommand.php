<?php


namespace Theaterjobs\MembershipBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\JobQueueBundle\Entity\Job as JobQueue;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Send notification to user when membership is about to expire
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class MembershipExpirationCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /** @DI\Inject("theaterjobs_membership.mailer")  */
    public $mailer;

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:expiration-membership')
            ->setDescription('Command for notifying user 40 days before the membership expires');
    }

    /**
     * Notification for users that their membership is ending in upcoming 40 days
     *
     * @param InputInterface $input user input
     * @param OutputInterface $output terminal interface
     *
     * @return void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $after40today = Carbon::today()->addDays(40);
        $this->em = $this->get("doctrine.orm.entity_manager");

        //Send notification and emails to users
        $bookings = $this->em->getRepository(Booking::class)->expiredMembershipBefore($after40today);
        $this->sendNotifications($bookings);
    }

    /**
     * Send notification and email to users
     *
     * @param Booking[] $bookings
     *
     * @return void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function sendNotifications($bookings)
    {
        $i = 0;
        $batchSize = 50;

        /** @var Booking $booking */
        foreach ($bookings as $booking) {
            $user =$booking->getProfile()->getUser();
            $billing = $booking->getLastBilling();
            $exists = $billing->getExpireEmail();

            if(!$exists){
                $this->sendNotification($user);
                // @todo this section needs to be uncommented when testing and in the relaunch
                // $this->mailer->sendMembershipExpirationEmail($user);

                //Set sentEmail to true to don't process twice or more
                $billing->setExpireEmail(true);
                $this->output->writeln("Sent notification to user with email : " . $user->getEmail());
                if ($i % $batchSize === 0) {
                    $this->em->flush();
                    $this->em->clear(Notification::class);
                    $this->em->clear(JobQueue::class);
                }
                $i++;
            }
        }

        $this->em->flush();
        $this->output->writeln(sprintf("Sent notification/email to %d users", $i));
    }

    /**
     * @param User $user
     */
    public function sendNotification($user)
    {
        $membershipPeriod = $user->getMembershipExpiresAt();
        if (!$membershipPeriod) {
            $this->output->writeln("!!! ERROR !!!");
            $this->output->writeln(sprintf("User with id %d has no membershipExpireAt value ", $user->getId()));
            return;
        }
        $link = 'tj_membership_booking_new';
        $transDescParams = ['%date%' => $membershipPeriod->format('d.m.Y')];
        $title = 'dashboard.notification.membership.expiration %date%';
        $description = 'dashboard.notification.membership.expiration.description %date%';
        $transParams = ['%date%' => $membershipPeriod->format('d.m.Y')];

        $notification = new Notification();
        $notification
            ->setLink($link)
            ->setTitle($title)
            ->setRequireAction(true)
            ->setCreatedAt(Carbon::now())
            ->setDescription($description)
            ->setTranslationKeys($transParams)
            ->setTranslationDescKeys($transDescParams);

        $event = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($user->getId())
            ->setNotification($notification)
            ->setFrom($user)
            ->setUsers($user)
            ->setType('membership_about_expire')
            ->setFlush(false);

        $this->get('event_dispatcher')->dispatch('notification', $event);
    }
}
