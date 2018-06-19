<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 10/03/2018
 * Time: 10:55
 */

namespace Theaterjobs\AdminBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\VioSearch;

/**
 * Class VioRepository
 * @package Theaterjobs\AdminBundle\Entity
 */
class VioRepository extends EntityRepository
{

    /**
     * @param VioSearch $vioSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(VioSearch $vioSearch)
    {
        $qb = $this->createQueryBuilder('v');
        $qb->innerJoin('v.organization', 'o')
            ->select('v.isChecked as checked,
                             v.id as id,
                             v.daysInterval as interval,
                             v.createdAt as createdAt,
                             o.name as organization,
                             o.slug as organizationSlug'
            );

        if ($vioSearch->getOrderCol()) {
            $qb->orderBy(sprintf("%s", $vioSearch->getOrderCol()), $vioSearch->getOrder());
        }

        return $qb->getQuery();
    }

}