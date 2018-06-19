<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 10/03/2018
 * Time: 10:56
 */

namespace Theaterjobs\AdminBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\VioReminderSearch;

/**
 * Class VioReminderRepository
 * @package Theaterjobs\AdminBundle\Entity
 */
class VioReminderRepository extends EntityRepository
{
    /**
     * @param VioReminderSearch $adminVioReminderSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(VioReminderSearch $adminVioReminderSearch)
    {
        $qb = $this->createQueryBuilder('vr');
        $qb->innerJoin('vr.vio', 'v')
            ->innerJoin('v.organization', 'o')
            ->select('
                     vr.id as id,
                     vr.createdAt as createdAt,
                     o.name as organization,
                     o.slug as organizationSlug'
            );

        if ($adminVioReminderSearch->getOrderCol()) {
            $qb->orderBy(sprintf("%s", $adminVioReminderSearch->getOrderCol()), $adminVioReminderSearch->getOrder());
        }

        return $qb->getQuery();
    }

}