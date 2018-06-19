<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{

    /**
     * Not used
     * @param $user
     * @return array
     */
    public function makeSeenIds($user)
    {
        $qb = $this->createQueryBuilder('n');
        $query = $qb->select('n.id as id')
            ->where('n.user = :user')
            ->andWhere('n.seen = 0')
            ->setParameter('user', $user)
            ->getQuery();

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }

    /**
     * Notification Required Action
     * @param $user
     *
     * @return array
     */
    public function NRA($user)
    {
        $qb = $this->createQueryBuilder('n')
        ->where('n.user = :user')
        ->andWhere('n.requireAction = 1')
        ->orderBy('n.createdAt', 'DESC')
        ->setParameters([
            'user' => $user
        ]);
        return $qb->getQuery()->getResult();
    }

    /**
     * Notification Informative
     * @param User $user
     * @param $maxResults
     *
     * @return array
     */
    public function NI($user, $maxResults)
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.requireAction = 0')
            ->andWhere('n.seen = 0')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameters(['user' => $user]);
        return $qb->getQuery()->getResult();
    }

    /**
     * Get all notification older than x date
     * @return Notification[]
     */
    public function olderThanIds($date)
    {
        $qb = $this->createQueryBuilder('n');
        $query = $qb
            ->select('n.id as id')
            ->where('n.createdAt < :date')
            ->andWhere('n.requireAction = false')
            ->setParameters(['date' => $date])
            ->getQuery();

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }


    /**
     * Get notification based on user and code type of notification
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function findByUserType($userId, $type)
    {
        $qb = $this->createQueryBuilder('n');

        $query = $qb->select('count(n.id)')
            ->innerJoin('n.user', 'u')
            ->innerJoin('n.typeOfNotification', 'ton')
            ->where('u.id = :userId')
            ->andWhere('ton.code = :type')
            ->setParameters(['userId' => $userId, 'type' => $type])
            ->getQuery();
            return $query->getSingleScalarResult();
    }
}