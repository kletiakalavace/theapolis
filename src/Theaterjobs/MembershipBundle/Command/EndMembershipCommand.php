<?php

namespace Theaterjobs\MembershipBundle\Command;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Event\MembershipExpiredEvent;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\MarkNotificationAsReadEvent;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Command for ending membership and sending notification
 * Class EndMembershipCommand
 * @package Theaterjobs\MembershipBundle\Command
 */
class EndMembershipCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight;

    /** @var  EntityManager */
    protected $em;

    /** @var EventDispatcher */
    protected $dispatcher;

    protected function configure()
    {
        $this->setName('theaterjobs:cron:end-membership')
            ->setDescription('Command for ending debit quited contract  membership and send notification')
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Simulate a day');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get("doctrine.orm.entity_manager");
        $this->dispatcher = $this->getContainer()->get('event_dispatcher');

        $i = 0;
        $batchSize = 50;
        $today = Carbon::today();
        $bookings = $this->em->getRepository(Booking::class)->expiredMembershipBefore($today);

        /** @var Booking $booking */
        foreach ($bookings as $booking) {
            $user = $booking->getProfile()->getUser();

            $event = new MembershipExpiredEvent($user);
            $event->setFlush(false);
            $this->dispatcher->dispatch(MembershipEvents::MEMBERSHIP_EXPIRED, $event);

            $this->membershipNotification($user);

            if (($i % $batchSize) === 0) {
                $this->em->flush(); // Executes all updates.
                $this->em->clear(Notification::class); // Detaches all objects from Doctrine!
                $this->em->clear(JobQueue::class); // Detaches all objects from Doctrine!
            }
            ++$i;
            $output->writeln("Ended membership of user with email " . $user->getEmail());
        }

        $this->em->flush();
        $output->writeln(sprintf("Ended membership of %d users.", $i));
    }

    /**
     * Send Membership Notifications
     * @param $user
     */
    private function membershipNotification($user)
    {
        $title = 'dashboard.notification.membership.ended';
        $description = 'dashboard.notification.membership.ended.description';
        $link = 'tj_membership_booking_new';

        $notification = new Notification();
        $notification
            ->setTitle($title)
            ->setCreatedAt(Carbon::now())
            ->setDescription($description)
            ->setRequireAction(false)
            ->setLink($link);

        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($user->getId())
            ->setNotification($notification)
            ->setUsers($user)
            ->setType('membership_ended')
            ->setFlush(false);

        $this->dispatcher->dispatch('notification', $notificationEvent);
        //Delete notification membership_about_expire
        $markNotificationReadEvent = new MarkNotificationAsReadEvent($user, 'membership_about_expire', $user, null, false);
        $this->dispatcher->dispatch("MarkNotificationAsReadEvent", $markNotificationReadEvent);
    }

}
