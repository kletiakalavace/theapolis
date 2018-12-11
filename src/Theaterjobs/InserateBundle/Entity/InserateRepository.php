<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Theaterjobs\AdminBundle\Model\JobRequestSearch;
use Theaterjobs\InserateBundle\Model\UserInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Repository for the Inserate.
 *
 * @category Repository
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class InserateRepository extends EntityRepository
{
    public function findInserateByConfirmationToken($token)
    {

        $qb = $this->_em->createQueryBuilder()
            ->select('i')
            ->from('TheaterjobsInserateBundle:Inserate', 'i')
            ->where('i.confirmationToken = :token')
            ->setParameter('token', $token);

        return $qb->getQuery()->getResult();
    }

    /**
     * Return all pending jobs of a user in all organizations is part of
     *
     * @param array $organizations ids
     * @return array
     */
    public function getRequestsTeamMembers($organizations)
    {
        if (is_array($organizations) && count($organizations) > 0) {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $requests = $qb->select('i')
                ->from('TheaterjobsInserateBundle:Job', 'i')
                ->where('i.status = 5')
                ->andWhere("i.pendingAction = 2 ")
                ->andWhere($qb->expr()->in('i.organization', $organizations))
                ->getQuery()->getResult();
            return $requests;
        }
        return [];
    }

    public function getPublishRequestsForUser($user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $requests = $qb->select('i')
            ->from('TheaterjobsInserateBundle:Job', 'i')
            ->where('i.status = 5')
            ->andWhere("i.user = :user ")
            ->setParameter('user', $user)
            ->getQuery()->getResult();
        return $requests;
    }
}
