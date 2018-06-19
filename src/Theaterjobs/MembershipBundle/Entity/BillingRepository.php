<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Doctrine\ORM\Query\Expr;

/**
 * BillingRepository
 *
 * @category Repository
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class BillingRepository extends EntityRepository {

    public function findOpenOrPendingByProfile(ProfileInterface $profile) {
        $qb = $this->createQueryBuilder("b");
        $qb->innerJoin('b.billingStatus', 's',
                Expr\Join::WITH, $qb->expr()->orX(
                    $qb->expr()->eq('s.name', ':status_open'),
                    $qb->expr()->eq('s.name', ':status_pending')
                )
            )
            ->innerJoin('b.booking', 'bo')
            ->innerJoin('bo.profile', 'p', Expr\Join::WITH, $qb->expr()->eq('p.id', ':profile_id'))
            ->setParameters([
                "profile_id" => $profile->getId(),
                "status_open" => BillingStatus::OPEN,
                "status_pending" => BillingStatus::PENDING,
            ]
        );
        $result = $qb->getQuery()->getResult();
        return $result ? $result[0] : null;
    }

    /**
     * Find billing by profile
     *
     * @param ProfileInterface $profile
     * @return array
     */
    public function findByProfile(ProfileInterface $profile) {
        $qb = $this->createQueryBuilder("b");
        $qb->innerJoin('b.booking', 'bo')
            ->innerJoin('bo.profile', 'p', Expr\Join::WITH, $qb->expr()->eq('p.id', ':profile_id'))
            ->orderBy('b.createdAt', 'DESC')
            ->setParameters([
                "profile_id" => $profile->getId(),
            ]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * Find open billings
     * @return array
     */
    public function findOpenBillings() {
        $qb = $this->createQueryBuilder("b");
        $qb->innerJoin('b.billingStatus', 's')
                ->innerJoin('b.booking', 'bo')
                ->innerJoin('bo.profile', 'p')
                ->where('s.name = :status_open')
                ->setParameters(array("status_open" => BillingStatus::OPEN)
        );
        $openBillings = $qb->getQuery()->getResult();
        return $openBillings;
    }

    /**
     * Get number of pending bills by profile
     * @param $profile
     * @return mixed
     */
    public function findPendingBillsByProfile($profile) {
        $bookings = $this->createQueryBuilder('bill')->select('COUNT(bill.id) AS pendingBills')
                        ->innerJoin('bill.booking', 'booking')
                        ->innerJoin('bill.billingStatus', 'status')
                        ->innerJoin('booking.profile', 'profile')
                        ->where('status.name = :sName')
                        ->andWhere('profile = :prof')
                        ->setParameters(array('sName' => BillingStatus::PENDING, 'prof' => $profile))
                        ->getQuery()->getResult();

        return $bookings[0];
    }

    /**
     * All billings that sepa is not generated for them
     * @return Billing[]
     */
    public function noSepaBillings()
    {
        $qb = $this->createQueryBuilder('bill');
        $qb->where('bill.downloadedSepa = false')
            ->andWhere('bill.sepa IS NOT NULL');

        return $qb->getQuery()
            ->getResult();
    }

    public function findOpenPaypalSofortBilling() {
        $qb = $this->createQueryBuilder("b");
        $qb->innerJoin('b.billingStatus', 's')
            ->innerJoin('b.booking', 'bo')
            ->innerJoin('bo.profile', 'p')
            ->innerJoin('bo.paymentmethod', 'pm')
            ->where('s.name = :status_open')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('pm.short', ':shortName'),
                $qb->expr()->eq('pm.short', ':shortName1')
            ))
            ->setParameters([
                "status_open" => BillingStatus::OPEN,
                "shortName" => Paymentmethod::PAYPAL,
                "shortName1" => Paymentmethod::SOFORT
            ]);

        return $qb->getQuery()->getResult();
    }
}
