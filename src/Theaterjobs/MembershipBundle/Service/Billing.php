<?php

namespace Theaterjobs\MembershipBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\Billing as BillingEntity;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * Billing Service
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Service
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_membership.billing")
 */
class Billing {

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * @DI\Inject("theaterjobs_membership.price")
     * @var \Theaterjobs\MembershipBundle\Service\Price
     */
    public $price;

    /**
     * Creates a Billing from a Booking.
     *
     * @param Booking $booking
     * @param Booking $calc
     * @return BillingEntity
     */
    public function createBilling(Booking $booking, $calc) {
        $billing = new BillingEntity();
        $membership = $booking->getMembership();
        $billing->setBooking($booking);

        $countryTaxRate = $this->price->getCountryTaxRate($booking);
        if (null != $countryTaxRate) {
            $billing->setTaxRate($countryTaxRate->getTaxRate());
        }

        $billing->setPaymentmethodPrice($this->price->getPaymentPrice($booking));
        // Total money
        $billing->setSumGross($calc['gross']);
        // Netto money
        $billing->setSumNet($calc['net']);
        // Vat money
        $billing->setSumVat($calc['vat']);
        // Total
        $billing->setTotal($calc['gross']);
        $start = new \DateTime();
        $billing->setTimePeriodStart($start);
        $end = (new \DateTime())->add(new \DateInterval($membership->getDuration()));
        $billing->setTimePeriodEnd($end);
        $this->em->persist($billing);
        return $billing;
    }

    /**
     * Gets an open or pending Billing if exists.
     *
     * @param ProfileInterface $profile
     * @return BillingEntity
     */
    public function getOpenOrPendingBilling(ProfileInterface $profile) {
        $repos = $this->em->getRepository('TheaterjobsMembershipBundle:Billing');
        $billingEntity = $repos->findOpenOrPendingByProfile($profile);

        return $billingEntity;
    }
    
    /**
     * Closes an open or pending Billing if exists.
     *
     * @param ProfileInterface $profile
     * @return BillingEntity
     */
    public function closeBilling(ProfileInterface $profile) {
        $billingEntity = $this->em->getRepository('TheaterjobsMembershipBundle:Billing')->findOpenOrPendingByProfile($profile);

        $billingStatus = $this->em->createQueryBuilder()
                        ->select("status")->from('TheaterjobsMembershipBundle:BillingStatus', 'status')
                        ->where('status.name = :name')
                        ->setParameter("name", \Theaterjobs\MembershipBundle\Entity\BillingStatus::STORNO)
                        ->getQuery()->getResult();

        $billingEntity->setStatus($billingStatus[0]);

        return $billingEntity;
    }
}
