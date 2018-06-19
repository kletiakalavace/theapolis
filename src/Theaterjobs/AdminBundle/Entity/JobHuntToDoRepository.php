<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\JobHuntToDoSearch;

/**
 * JobHuntRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class JobHuntToDoRepository extends EntityRepository
{
    /**
     * Search by name
     * @param $name
     * @return \Doctrine\ORM\Query
     */
    public function getJobHuntToDos($name)
    {
        $qb = $this->createQueryBuilder('jh');
        $qb->innerJoin('jh.jobHunt', 'j');
        if ($name) {
            $qb->where('j.name like :name')
                ->setParameter('name', $name);
        }
        return $qb->getQuery();
    }

    /**
     * Search by form params
     * @param JobHuntToDoSearch $adminJobHuntToDoSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(JobHuntToDoSearch $adminJobHuntToDoSearch)
    {
        $qb = $this->createQueryBuilder('jh');
        $qb->innerJoin('jh.jobHunt', 'j');
        $qb->select('j.name as name, j.description as description, j.url as url, jh.createdAt as createdAt,jh.id as id');

        if ($adminJobHuntToDoSearch->getName()) {
            $qb
                ->where($qb->expr()->like('j.name', ':name'))
                ->setParameter('name', sprintf('%%%s%%', $adminJobHuntToDoSearch->getName()));
        }

        if ($adminJobHuntToDoSearch->getOrderCol()) {
            $qb->orderBy(sprintf("%s", $adminJobHuntToDoSearch->getOrderCol()), $adminJobHuntToDoSearch->getOrder());
        }

        return $qb->getQuery();
    }


    /**
     * Search by name
     * @param $name
     * @return \Doctrine\ORM\Query
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNrJobHuntToDos($name)
    {
        $qb = $this->createQueryBuilder('jh');
        $qb->innerJoin('jh.jobHunt', 'j')
            ->select('count(jh.id) as total');

        if ($name) {
            $qb->where('j.name like :name')
                ->setParameter('name', $name);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}