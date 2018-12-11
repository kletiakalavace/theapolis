<?php

namespace Theaterjobs\StatsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Carbon\Carbon;
use Doctrine\ORM\Query\Expr\Comparison;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Description of ViewRepository
 *
 * @author abame
 */
class ViewRepository extends EntityRepository
{
    /**
     * @param $className
     * @param $id
     * @return array
     * @TODO Refactor
     */
    public function profileLastTenDaysViews($className, $id)
    {
        $today = Carbon::now()->format('Y-m-d H:i:s');
        $tenDaysAgo = Carbon::now()->subDay(10)->format('Y-m-d H:i:s');

        $qb = $this->createQueryBuilder('stats');
        $qb->select('stats, count(stats), stats.createdAt')
            ->where('stats.foreignKey= :objectId')
            ->andWhere('stats.objectClass = :class')
            ->andWhere('stats.createdAt BETWEEN :start AND :end')
            ->setParameters([
                'objectId' => $id,
                'class' => '%' . $className,
                'start' => $tenDaysAgo,
                'end' => $today
            ]);

        $qb->orderBy('stats.createdAt', 'DESC')->groupBy('stats.user');

        $stats = $qb->getQuery()->getResult();

        $statsArray = array();

        foreach ($stats as $key => $stat) {
            if ($stat[0]->getUser() !== null ) {
                $statsArray[$key]['profile'] = $stat[0]->getUser()->getProfile();
            } else {
                $statsArray[$key]['profile'] = null;
            }
            $statsArray[$key]['statCount'] = $stat[1];
            $statsArray[$key]['date'] = $stat['createdAt'];

            if (($stat[0]->getUser() !== null) && (count($stat[0]->getUser()->getProfile()->getMediaImage()->getValues()) > 0)) {
                foreach ($stat[0]->getUser()->getProfile()->getMediaImage() as $image) {
                    if ($image->getIsProfilePhoto()) {
                        $statsArray[$key]['image'] = $image;
                    }
                }
            } else {
                $statsArray[$key]['image'] = null;
            }
        }

        return $statsArray;
    }

    /**
     * @param $className
     * @param $id
     * @return mixed
     */
    public function deleteViewsIds($className, $id)
    {
        $qb = $this->createQueryBuilder('stat');
        $query = $qb->select('stat.id as id')
            ->where('stat.foreignKey= :objectId')
            ->andWhere('stat.objectClass = :class')
            ->setParameters([
                'objectId' => $id,
                'class' => $className
            ])->getQuery();

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }

    /**
     * Delete views prior than date for an object with specific id.
     * @param $className
     * @param $id
     * @param $date
     * @return mixed
     */
    public function deleteViewsPriorThanIds($className, $id, $date)
    {
        $qb = $this->createQueryBuilder('stat');

        $query = $qb->select('stat.id as id')
            ->where('stat.foreignKey= :objectId')
            ->andWhere('stat.objectClass = :class')
            ->andWhere("stat.createdAt < :date")
            ->setParameters([
                'objectId' => $id,
                'class' => $className,
                'date' => $date
            ])->getQuery();

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }

    /**
     * Return all ids of all views for an object class before x date.
     * @param $className
     * @param $date
     * @param array $onlyIds get only objects with fk in $onlyIds
     * @return mixed
     */
    public function getDeleteObjectViewsBeforeIds($className, $date, $onlyIds = [])
    {
        $qb = $this->createQueryBuilder('stat');

        $query = $qb->select('stat.id as id')
            ->where('stat.objectClass = :class')
            ->andWhere("stat.createdAt < :date")
            ->setParameters(['class' => $className, 'date' => $date]);

        if ($onlyIds) {
            $query->andWhere('stat.foreignKey in (:ids)')
                ->setParameter('ids', $onlyIds);
        }

        $result = $query->getQuery()->getResult();
        return array_reduce($result, function ($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }
}
