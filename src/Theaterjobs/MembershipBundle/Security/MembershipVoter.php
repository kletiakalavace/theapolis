<?php

namespace Theaterjobs\MembershipBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class MembershipVoter
 * @package Theaterjobs\MembershipBundle\Security
 * @DI\Service("theaterjobs_membership.membership_voter", public=false)
 * @DI\Tag("security.voter")
 */
class MembershipVoter extends Voter
{

    const CAN_SEE_INVOICE = 'can_see_invoices';
    const CAN_DOWNLOAD_BILL = 'can_download_bill';
    const CAN_DOWNLOAD_SEPA = 'can_download_sepa';

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
        return in_array($attribute, [self::CAN_SEE_INVOICE, self::CAN_DOWNLOAD_BILL, self::CAN_DOWNLOAD_SEPA]);
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
            case self::CAN_SEE_INVOICE:
                return $this->canSeeInvoice($subject, $user);
            case self::CAN_DOWNLOAD_BILL:
                return $this->canDownloadBill($subject, $user);
            case self::CAN_DOWNLOAD_SEPA:
                return $this->canDownloadSepa($subject, $user);
        }
        return false;
    }

    /**
     * @param Profile $profile
     * @param User $user
     * @return bool
     */
    public function canSeeInvoice(Profile $profile, $user)
    {
         // Check if this user is allowed to see billings of this profile
        return $user->hasRole(User::ROLE_ADMIN) || $profile->getSlug() == $user->getProfile()->getSlug();
    }
    /**
     * @param Billing $billing
     * @param User $user
     * @return bool
     */
    public function canDownloadBill(Billing $billing, $user)
    {
        // If Admin
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        };
        // Check if this user is allowed to download this bill
        $isHisBill = $billing->getBooking()->getProfile()->getUser()->getId() === $user->getId();
        return $isHisBill;
    }
    /**
     * @param Billing $billing
     * @param User $user
     * @return bool
     */
    public function canDownloadSepa(Billing $billing, $user)
    {
        // Only debit has sepa billings
        $isDebit = $billing->getBooking()->getPaymentmethod()->isDebit();
        // If Admin
        if ($user->hasRole(User::ROLE_ADMIN) && $isDebit) {
            return true;
        };
        // Check if this user is allowed to download this bill
        $isHisBill = $billing->getBooking()->getProfile()->getUser()->getId() === $user->getId();
        return $isHisBill && $isDebit;
    }
}