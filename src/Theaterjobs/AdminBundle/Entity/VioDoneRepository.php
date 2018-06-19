<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 10/03/2018
 * Time: 10:56
 */

namespace Theaterjobs\AdminBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\VioDoneSearch;

/**
 * Class VioDoneRepository
 * @package Theaterjobs\AdminBundle\Entity
 */
class VioDoneRepository extends EntityRepository
{

    /**
     * @param VioDoneSearch $vioDoneSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(VioDoneSearch $vioDoneSearch)
    {
        $qb = $this->createQueryBuilder('vd');
        $qb->innerJoin('vd.organization', 'o')
            ->innerJoin('vd.profile', 'p')
            ->select('
                     vd.comment as comment,
                     vd.createdAt as createdAt,
                     o.name as organization,
                     o.slug as organizationSlug,
                     p.slug as profileSlug,
                     CONCAT(p.firstName, \' \', p.lastName) as user'
            );

        if ($vioDoneSearch->getOrderCol()) {
            $qb->orderBy(sprintf("%s", $vioDoneSearch->getOrderCol()), $vioDoneSearch->getOrder());
        }

        return $qb->getQuery();
    }

}