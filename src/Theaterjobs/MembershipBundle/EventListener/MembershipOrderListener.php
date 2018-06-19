<?php

namespace Theaterjobs\MembershipBundle\EventListener;

use Carbon\Carbon;
use Theaterjobs\MembershipBundle\Event\OrderEvent;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * MembershipOrderListener
 *
 * @category EventListener
 * @package  Theaterjobs\MembershipBundle\EventListener
 * @author   Jurgen Rexhmati
 *
 * @DI\Service("theaterjobs_membership.orderlistener.class")
 * @DI\Tag("kernel.event_listener", attributes = {"event" = "membership.order", "method"="onMembershipOrder"})
 */
class MembershipOrderListener
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;
    /** @DI\Inject("translator") */
    public $translator;
    /** @DI\Inject("router") */
    public $router;
    /** @DI\Inject("event_dispatcher") */
    public $dispatcher;

    /**
     * Listens to the membership.order event to set the right expire
     * time of the membership and the right ROLE of the user.
     *
     * @param OrderEvent $event
     */
    public function onMembershipOrder(OrderEvent $event)
    {
        $billing = $event->getBilling();
        $booking = $billing->getBooking();
        $payment = $booking->getPaymentmethod();

        // Get user from db with all fields
        $user = $this->em->getRepository(User::class)->find($booking->getProfile()->getUser()->getId());

        if ($payment->isDebit()) {
            $this->doDirectDebit($billing, $user);
        }

        if ($payment->isPaypal()) {
            $this->doPaypal($billing, $user->getProfile());
        }

        if ($payment->isSofort()) {
            $this->doSofort($billing, $user);
        }
    }

    /**
     * Performs actions on user and billing those correspond to
     * the Paymentmethod DirectDebit
     *
     * @param Billing $billing
     * @param User $user
     */
    private function doDirectDebit(Billing $billing, User $user)
    {
        if ($user->getRecuringPayment()) {
            $billing->setSequence('RCUR');
        } else {
            $billing->setSequence('FRST');
        }

        $user->setBankConfirmed(false);
        if ($user->getHasRequiredRecuringPaymentCancel()) {
            $user->setRecuringPayment(false);
        } else {
            $user->setRecuringPayment(true);
        }
        $billing->setDownloadedSepa(false);

        $this->giveMemberRoleToUser($user);
        $this->setMembershipExpiredToUser($user);

        $repository = $this->em->getRepository(BillingStatus::class);
        $status = $repository->findOneByName(BillingStatus::PENDING);

        $user->setquitContractDate(null);
        $user->setQuitContract(false);
        $billing->setBillingStatus($status);

        $this->em->persist($billing);
    }

    /**
     * Performs actions on user and billing those correspond to
     * the Paymentmethod Paypal
     *
     * @param Billing $bill
     * @param Profile $profile
     */
    private function doPaypal($bill, $profile)
    {
        $user = $profile->getUser();
        $this->giveMemberRoleToUser($user);
        $this->setMembershipExpiredToUser($user);

        $user->setBankConfirmed(true);

        $repository = $this->em->getRepository(BillingStatus::class);
        $status = $repository->findOneByName(BillingStatus::COMPLETE);
        $user->setquitContractDate(null);

        $bill->setBillingStatus($status);
        $this->em->persist($user);
    }

    /**
     * @param Billing $bill
     * @param User $user
     */
    public function doSofort($bill, $user)
    {
        $user->setBankConfirmed(true);
        $user->setRecuringPayment(false);
        $user->setquitContractDate(null);

        $this->giveMemberRoleToUser($user);
        $this->setMembershipExpiredToUser($user);

        $status = $this->em->getRepository(BillingStatus::class)->findOneByName(BillingStatus::COMPLETE);
        $bill->setBillingStatus($status);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * if the user has a membership expires at and the difference between membership
     *      expires at and now is greater than now (meaning it will expire after x days)
     *      add one year to that membership expires at
     * else
     *      add one year from today
     *
     * @param User $user
     */
    private function setMembershipExpiredToUser($user)
    {
        if ($user->getMembershipExpiresAt() != null && Carbon::instance($user->getMembershipExpiresAt())->gte(Carbon::now())) {
            // set the membership expires at from the membershipExpiresAt to 1 year +
            $user->setMembershipExpiresAt(Carbon::instance($user->getMembershipExpiresAt())->addYear());
        } else {
            $user->setMembershipExpiresAt(Carbon::now()->addYear());
        }
    }

    /**
     * Add role member to the user
     * @param User $user
     */
    private function giveMemberRoleToUser($user)
    {
        if ($user->hasRole('ROLE_USER')) {
            $user->addRole('ROLE_MEMBER');
            $user->removeRole('ROLE_USER');
        } else {
            $user->addRole('ROLE_MEMBER');
        }
    }
}
