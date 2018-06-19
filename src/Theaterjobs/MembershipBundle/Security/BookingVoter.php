<?php

namespace Theaterjobs\MembershipBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class BookingVoter
 * @package Theaterjobs\MembershipBundle\Security
 * @DI\Service("theaterjobs_membership.booking_voter", public=false)
 * @DI\Tag("security.voter")
 */
class BookingVoter extends Voter
{
    const BUY_MEMBERSHIP = "can_buy_membership";

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /** @DI\Inject("translator") */
    public $trans;

    /** @DI\Inject("session") */
    public $session;

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
        return in_array($attribute, [self::BUY_MEMBERSHIP]);
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

        switch ($attribute) {
            case self::BUY_MEMBERSHIP:
                return $this->canBuyMembership($user);
        }
        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    private function canBuyMembership($user)
    {
        $profile = $user->getProfile();

        $billings = $this->em->getRepository(Billing::class)->findPendingBillsByProfile($profile);
        if ($billings['pendingBills'] > 0) {
            $this->addFlash('dashboard', ['error' => $this->trans->trans("flash.error.membership.pending.exists")]);
            return false;
        }

        // Currently on a debit contract
        $currPayMethod = $this->em->getRepository(Paymentmethod::class)->paymentMethodByProfile($profile);
        if ($currPayMethod && $currPayMethod->isDebit() && $user->getMembershipExpiresAt() && !$user->getQuitContract()) {
            $this->addFlash('dashboard', ['error' => $this->trans->trans("flash.error.membership.debit.contract.exists")]);
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @param array|string $message
     */
    protected function addFlash($type, $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}