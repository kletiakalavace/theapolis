<?php

namespace Theaterjobs\UserBundle\Services;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Description of AccountSettings
 *
 * @category CATEGORY
 * @author   Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @DI\Service("theaterjobs_user_bundle.account_settings")
 */
class AccountSettings {

    /**
     * @DI\Inject("theaterjobs_membership.sepa")
     * @var Sepa
     */
    public $sepa;

    /**
     * @DI\Inject("twig")
     */
    public $twig;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var ObjectManager
     */
    public $em;

    /**
     * Get membership Block
     * @param Profile $profile
     *
     * @return
     */
    public function getMembershipBlockData(Profile $profile)
    {
        $booking = $profile->getLastBooking();
        $billing = $booking ? $booking->getLastBilling() : null;
        $paymentMethod = $booking ? $booking->getPaymentMethod() : null;
        $expireDate = null;
        $diffInDays = null;

        $debitAccount = $profile->getDebitAccount();
        $bankName = $debitAccount ? $this->sepa->generateBic($debitAccount->getIban()) : null;

        if ($billing && $profile->getUser()->getMembershipExpiresAt()) {
            $expireDate = $profile->getUser()->getMembershipExpiresAt();
            $diffInDays = $expireDate->diff(Carbon::now())->days;
        }

        return [
            'paymentMethod' => $paymentMethod ?: null,
            'expireDate' => $expireDate,
            'showBuyMembership' => $diffInDays < 90,
            'debitAccount' => $debitAccount,
            'bankName' => $bankName,
            'owner' => false,
            'profile' => $profile
        ];
    }
    /**
     * Get membership Block
     * @param Profile $profile
     *
     * @return
     */
    public function getMembershipBlock(Profile $profile)
    {
        $data = $this->getMembershipBlockData($profile);
        return $this->twig->render('@TheaterjobsUser/Partial/EmailChangeRequest/membershipBlock.html.twig', $data);
    }

    /**
     * Get activity logs html content
     * @param Profile $profile
     * @return mixed
     */
    public function getActivityBlock(Profile $profile)
    {
        $activityLogs = $this->getActivityLogs($profile);
        $data = [
            'profile' => $profile,
            'options' => $activityLogs
        ];
        return $this->twig->render('@TheaterjobsUser/AccountSettings/Partial/activity.html.twig', $data);
    }

    /**
    * Get activity logs data
    * @param Profile $profile
    * @return array
    */
    public function getActivityLogs(Profile $profile)
    {
        $user = $profile->getUser();
        $activityLogs = $this->em->getRepository('TheaterjobsUserBundle:UserActivity')->findBy(['user' => $user], ['id' => 'desc']);
        $activityOptions = [
            'allEntitiesPath' => 'tj_user_account_settings_all_logs',
            'slug' => $profile->getSlug()
        ];
        return [
            'activityLogs' => $activityLogs,
            'activityOptions' => $activityOptions
        ];
    }
}
