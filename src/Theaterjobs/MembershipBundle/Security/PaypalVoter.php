<?php

namespace Theaterjobs\MembershipBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class PaypalVoter
 * @package Theaterjobs\MembershipBundle\Security
 * @DI\Service("theaterjobs_membership.paypal_voter", public=false)
 * @DI\Tag("security.voter")
 */
class PaypalVoter extends Voter
{

    const MAKE_PAYMENT = 'make_payment_using_paypal';
    const EXEC_PAYMENT = 'execute_payment_using_paypal';
    const CANCEL_PAYMENT = 'cancel_payment_using_paypal';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::MAKE_PAYMENT, self::EXEC_PAYMENT, self::CANCEL_PAYMENT]);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        /** @var Billing $billing */
        $billing = $subject;

        switch ($attribute) {
            case self::MAKE_PAYMENT:
                return $this->canMakePayment($billing, $user);
            case self::EXEC_PAYMENT:
                return $this->canExecPayment($billing, $user);
            case self::CANCEL_PAYMENT:
                return $this->canCancelPayment($billing, $user);
        }

        return false;
    }

    /**
     * @param Billing $billing
     * @param User $user
     * @return bool
     */
    public function canMakePayment($billing, $user)
    {
        // Check if the billing belongs to current authenticated user
        $isOwner = $billing->getBooking()->getProfile()->getUser()->isEqual($user);
        $isOpen = $billing->getBillingStatus()->isOpen();
        return $isOwner && $isOpen;
    }

    /**
     * @param Billing $billing
     * @param User $user
     * @return bool
     */
    public function canExecPayment($billing, $user)
    {
        // Check if the billing belongs to current authenticated user
        $isOwner = $billing->getBooking()->getProfile()->getUser()->isEqual($user);
        $isOpen = $billing->getBillingStatus()->isOpen();
        return $isOwner && $isOpen;
    }

    /**
     * @param Billing $billing
     * @param User $user
     * @return bool
     */
    public function canCancelPayment($billing, $user)
    {
        // Check if the billing belongs to current authenticated user
        $isOwner = $billing->getBooking()->getProfile()->getUser()->isEqual($user);
        $isOpen = $billing->getBillingStatus()->isOpen();
        return $isOwner && $isOpen;
    }
}