<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for the PaymentmethodRepository.
 *
 * @category Repository
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class SepaMandateRepository extends EntityRepository
{

    public function findSepaByProfile($profile)
    {

        $qb = $this->createQueryBuilder('sm');
        $qb->where('sm.profile = :profile');
        $qb->setParameter('profile', $profile);
        $sepaMandates = $qb->getQuery()->getResult();

        return $sepaMandates;
    }

    public function findSepaCurrentSepa($profile)
    {

        $qb = $this->createQueryBuilder('sm');
        $qb->where('sm.profile = :profile')
            ->setParameter('profile', $profile)
            ->orderBy('sm.id', 'DESC')
            ->setMaxResults(1);
        $sepaMandates = $qb->getQuery()->getResult();

        return $sepaMandates;
    }

}
