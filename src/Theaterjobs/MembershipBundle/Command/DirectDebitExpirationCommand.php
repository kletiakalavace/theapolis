<?php

namespace Theaterjobs\MembershipBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *  Remind the user via email that his payment period ends in 20 days
 *
 * @package Theaterjobs\MembershipBundle\Command
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class DirectDebitExpirationCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /** @var  EntityManager */
    protected $em;

    /** @var  EventDispatcherInterface */
    private $dispatcher;

    /** @var  OutputInterface */
    private $output;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:direct-debit-expiration')
            ->setDescription('Remind the user via notification that his payment period ends in 20 days');
    }

    /**
     *  Main logic of the command
     *
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->dispatcher = $this->get('event_dispatcher');
        $this->em = $this->get("doctrine.orm.entity_manager");
        $after20today = Carbon::today()->addDays(20);

        $bookings = $this->em->getRepository(Booking::class)->expiredDebitMembershipBefore($after20today);
        $this->sendNotifications($bookings);
    }

    /**
     * Send email to user, log this activity to administrator
     *
     * @param Booking[] $bookings
     */
    public function sendNotifications($bookings)
    {
        $i = 0;
        $batchSize = 50;
        foreach ($bookings as $booking) {
            $billing = $booking->getLastBilling();
            if (!$billing->getExpireEmail()) {
                $billing->setExpireEmail(true);
                $user = $booking->getProfile()->getUser();
                $this->sendNotification($billing, $user);

                if ($i % $batchSize === 0) {
                    $this->em->flush();
                    $this->em->clear(Notification::class);
                }
                ++$i;
            }
        }
        $this->em->flush();
        $this->output->writeln(sprintf("Sent notification to %d users", $i));
    }

    /**
     * Send notification to user
     *
     * @param Billing $billing
     * @param User $user
     */
    public function sendNotification($billing, $user)
    {
        $title = 'dashboard.notification.membership.checkBankData';
        $link = 'tj_user_account_settings';
        $linkParams = ['slug' => $user->getProfile()->getSlug()];

        $notification = new Notification();
        $notification->setTitle($title)
            ->setDescription(' ')
            ->setRequireAction(false)
            ->setLink($link)
            ->setLinkKeys($linkParams);

        $event = (new NotificationEvent())
            ->setObjectClass(Billing::class)
            ->setObjectId($billing->getId())
            ->setNotification($notification)
            ->setFrom($user)
            ->setUsers($user)
            ->setType('membership_dd_check_bank_data')
            ->setFlush(false);

        $this->output->writeln(sprintf("Sent notification to : %s", $user->getProfile()->getFullName()));
        $this->dispatcher->dispatch('notification', $event);
    }
}