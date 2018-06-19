<?php

namespace Theaterjobs\MembershipBundle\Command;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\ReadNotificationTrait;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Event\OrderEvent;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\MembershipBundle\Service\Price;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * Class GenerateBillCommand
 * @package Theaterjobs\MembershipBundle\Command
 * @DI\Service
 * @DI\Tag("console.command")
 */
class GenerateBillCommand extends ContainerAwareCommand
{
    use ContainerTrait;
    use ReadNotificationTrait;
    use ESUserActivity;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var ObjectManager
     */
    public $em;

    /**
     * @DI\Inject("event_dispatcher")
     * @var EventDispatcher
     */
    public $dispatcher;

    /**
     * @DI\Inject("theaterjobs_membership.billing")
     * @var \Theaterjobs\MembershipBundle\Service\Billing
     */
    public $billingService;

    /** @DI\Inject("translator") */
    public $trans;

    /**
     * @DI\Inject("theaterjobs_membership.price")
     * @var Price
     */
    public $priceService;

    /** @var OutputInterface */
    private $output;

    /** @var Membership */
    private $membership;


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('app:create:billing')
            ->addArgument('userId', InputArgument::REQUIRED, 'User Id')
            ->setDescription('Command to create billing for a recurring user');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->membership = $this->em->getRepository(Membership::class)->findOneBySlug(Membership::yearly);
        $this->output = $output;

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($input->getArgument('userId'));
        $profile = $user->getProfile();
        $lastBooking = $profile->getLastBooking();
        $payMethod = $lastBooking->getPaymentmethod();
        $lastBill = $lastBooking->getLastBilling();
        $isBlocked = $profile->getBlockedPaymentmethods()->contains($payMethod);

        if ($payMethod->isDebit() && !$isBlocked && $lastBill->isCompleted()) {
            $billing = $this->createBilling($profile, $lastBooking);
            // Reset flags
            $this->dispatcher->dispatch(MembershipEvents::MEMBERSHIP_ORDER, new OrderEvent($billing));
            // If the user has cancel the contract set requiring payment false
            if ($user->getHasRequiredRecuringPaymentCancel()) {
                $user->setRecuringPayment(false);
            }
            // Remove membership Notifications
            $this->readNotification($user, 'membership_about_expire', $user);
            $this->sendNotification($billing, $user);
            // Log user activity
            $user = $profile->getUser();
            $log = $this->trans->trans('tj.user.activity.extended.membership', []);
            $this->logUserActivity($user, $log, true, [], $user);
            $this->output->writeln("Extended membership of user with email " . $user->getEmail());
        }
    }

    /**
     * Send notification to user that we will recieve money
     * @param Billing $newBilling
     * @param User $user
     */
    private function sendNotification($newBilling, $user)
    {
        $notification = new Notification();
        $notification
            ->setTitle($this->get('translator')->trans("notification.theapolis.will.get.money.mail.subject"))
            ->setCreatedAt(new \DateTime())
            ->setDescription('')
            ->setRequireAction(false)
            ->setLink('tj_user_account_settings')
            ->setLinkKeys(['tab' => 'billing']);

        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(Billing::class)
            ->setObjectId($newBilling->getId())
            ->setNotification($notification)
            ->setUsers($user)
            ->setType('order_received');

        $this->dispatcher->dispatch('notification', $notificationEvent);
    }

    /**
     * @param Profile $profile
     * @param Booking $booking
     * @return Billing
     */
    private function createBilling(Profile $profile, Booking $booking)
    {
        $debitAcc = $profile->getDebitAccount();
        $billAdr = $profile->getBillingAddress();
        $calc = $this->priceService->calculateToSave($billAdr->getCountry(), $this->membership, $billAdr->getVatId(), false);
        $lastSepa = $profile->getLastSepaMandate() ?: $this->get('theaterjobs_membership.sepa')->generateSepa($profile);
        //Create new Bill
        $billing = $this->billingService->createBilling($booking, $calc);
        // Set billing data
        $billing->setSepa($lastSepa);
        $billing->setIban($debitAcc->getIban());
        $billing->setAccountHolder($debitAcc->getAccountHolder());
        $billing->setBillingAddress($billAdr->serialize());
        // Return new Billing
        return $billing;
    }
}