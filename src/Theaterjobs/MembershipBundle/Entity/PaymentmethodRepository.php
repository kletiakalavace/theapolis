<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Repository for the PaymentmethodRepository.
 *
 * @category Repository
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class PaymentmethodRepository extends EntityRepository {

    /** @DI\Inject("%theaterjobs_membership.profile_class%") */
    public $profileClass;

    /**
     * Get all Paymentmethods that are active and not blocked by that profile.
     *
     * @param Profile $profile
     * @return QueryBuilder
     */
    public function findByProfile(Profile $profile) {
        $qb = $this->createQueryBuilder('pm');
        $qb1 = $this->createQueryBuilder('pm1');

        $qb1->select('pm1.id')
            ->innerJoin('pm1.blockedForProfiles', 'p')
            ->where('p.id = :p_id');

        $qb->where($qb->expr()->notIn('pm.id', $qb1->getDQL()))
            ->andWhere('pm.isActive = true')
            ->setParameter('p_id', $profile->getId());

        return $qb;
    }

    /**
     * @param $profile
     * @return null | Paymentmethod
     */
    public function paymentMethodByProfile(Profile $profile) {
        $lastBooking = $profile->getLastBooking();
        return $lastBooking ? $lastBooking->getPaymentmethod() : null;
    }
    
    public function allowedForProfile($profile){
        $qb = $this->findByProfile($profile);
        $payments = $qb->getQuery()->getResult();
        return $payments;
    }

}
