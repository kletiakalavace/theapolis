<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\MembershipBundle\EventListener;

use Theaterjobs\MembershipBundle\Entity\Billing;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Description of BillingListener
 *
 * @category EventListener
 * @package  Theaterjobs\MembershipBundle\EventListener
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class BillingListener {

    public function prePersist(Billing $billing, LifecycleEventArgs $event) {
        $entityManager = $event->getEntityManager();
        $repository = $entityManager->getRepository('TheaterjobsMembershipBundle:BillingStatus');
        $status = $repository->findOneByName('open');
        $billing->setBillingStatus($status);
    }

    public function postPersist(Billing $billing, LifecycleEventArgs $event) {
        $entityManager = $event->getEntityManager();
        $booking = $billing->getBooking();
        $paymentmethod = $booking->getPaymentmethod();
        $profile = $booking->getProfile();
        $billingAddr = $profile->getBillingAddress();
        $countryCode = $billingAddr->getCountry();
        $nr = 100000000+$billing->getId();
        $billing->setNumber($countryCode . $nr);
        $billing->setPath('theapolis-' . $countryCode . '-' . preg_replace("/\s+/", '_', $paymentmethod->getTitle()) . '-' . $billing->getNumber() . '.pdf');

        $entityManager->persist($billing);
        $entityManager->flush();
    }

}
