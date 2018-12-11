<?php

namespace Theaterjobs\InserateBundle\Entity;

use DateTime;

/**
 * Repository for the Education.
 *
 * @category Repository
 * @package  Theaterjobs\InserateBundle\Entity
 */
class EducationRepository extends InserateRepository
{
    public function createEducationQuery($form) {
        $searchData = $form->getData();
        $qb = $this->getEntityManager()->createQueryBuilder();

        $params = [];
        if ($searchData['dateFrom'] != $searchData['dateTo']) {
            $qb->select('SUBSTRING(j.createdAt, 1, 10) as dt,COUNT(j) as num');
        } else {
            $qb->select('SUBSTRING(j.createdAt, 1, 19) as dt,COUNT(j) as num');
        }
        $qb->from('TheaterjobsInserateBundle:Education', 'j');
        if ($searchData['status']) {
            if ($searchData['status'] == 'published') {
                $qb->andWhere('j.archivedAt IS NULL')
                        ->andWhere('j.destroyedAt IS NULL');
            }
            if ($searchData['status'] == 'archived') {
                $qb->andWhere('j.archivedAt IS NOT NULL OR j.destroyedAt IS NOT NULL');
            }
        }
        if ($searchData['dateFrom']) {
            $qb->andWhere('j.createdAt >= :startDate');
            $params['startDate'] = new DateTime($searchData['dateFrom']);
        }
        if ($searchData['dateTo']) {
            $qb->andWhere('j.createdAt < :endDate');
            $date = new DateTime($searchData['dateTo']);
            $date->modify('+1 day');
            $params['endDate'] = $date;
        }
        $qb->groupBy('dt');
        $qb->setParameters($params);

        $educations = $qb->getQuery()->getResult();
        return $educations;
    }
}
