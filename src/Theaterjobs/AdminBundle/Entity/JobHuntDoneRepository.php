<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\JobHuntDoneSearch;

/**
 * Class JobHuntDoneRepository
 * @package Theaterjobs\AdminBundle\Entity
 */
class JobHuntDoneRepository extends EntityRepository
{

    /**
     * @param JobHuntDoneSearch $jobHuntDoneSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(JobHuntDoneSearch $jobHuntDoneSearch)
    {

        $qb = $this->createQueryBuilder('jhd');
        $qb->select('jhd.name as name, jhd.comment as comment, jhd.createdAt as createdAt, CONCAT(p.firstName, \' \', p.lastName) as user');
        $qb->innerJoin('jhd.profile', 'p');


        if ($jobHuntDoneSearch->getName()) {
            $qb
                ->where($qb->expr()->like('jhd.name', ':name'))
                ->setParameter('name', sprintf('%%%s%%', $jobHuntDoneSearch->getName()));
        }

        if ($jobHuntDoneSearch->getOrderCol()) {
            $qb->orderBy(sprintf("%s", $jobHuntDoneSearch->getOrderCol()), $jobHuntDoneSearch->getOrder());
        }

        return $qb->getQuery();
    }

    /**
     * Search by name
     * @param $name
     * @return \Doctrine\ORM\Query
     */
    public function getJobHuntsDone($name)
    {
        $qb = $this->createQueryBuilder('jhd');
        if ($name) {
            $qb->where('jhd.name like :name')
                ->setParameter('name', $name);
        }
        return $qb->getQuery();
    }

    /**
     * Search by name
     * @param $name
     * @return \Doctrine\ORM\Query
     */
    public function getNrJobHuntsDone($name)
    {
        $qb = $this->createQueryBuilder('jhd');
        $qb->select('count(jhd.id) as total');
        if ($name) {
            $qb->where('jhd.name like :name')
                ->setParameter('name', $name);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}