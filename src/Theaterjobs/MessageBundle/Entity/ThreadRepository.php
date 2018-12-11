<?php

namespace Theaterjobs\MessageBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Class ThreadRepository
 * @package Theaterjobs\MessageBundle\Entity
 */
class ThreadRepository extends EntityRepository
{

    /**
     * Get all threads the user is participant
     *
     * @param $user
     *
     * @return array [createdAt, Thread, nrUnseen]
     */
    public function getAllThreads($user)
    {
        $qb = $this->createQueryBuilder("thread");
        $qb->innerJoin('thread.metadata', 'threadMeta')
            ->innerJoin('thread.messages', 'm')
            ->where($qb->expr()->eq('threadMeta.participant', ':me'))
            ->andWhere($qb->expr()->eq('threadMeta.isDeleted', 'false'))
            ->groupBy('thread')
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'me' => $user,
            ]);
        return $qb->getQuery();
    }

    /**
     * Get number of unread messages of a $thread
     *
     * @param integer $thread
     * @param User $user
     *
     * @return array
     */
    public function getUnreadMessages($thread, $user)
    {
        $qb = $this->createQueryBuilder('thread');
        $qb->innerJoin('thread.messages', 'm')
            ->innerJoin('m.metadata', 'msgMeta')
            ->where($qb->expr()->neq('msgMeta.isRead', 'true'))
            ->andWhere($qb->expr()->eq('thread.id', ':threadId'))
            ->andWhere($qb->expr()->eq('msgMeta.participant', ':user'))
            ->setParameters(array(
                    'threadId' => $thread,
                    'user' => $user
                )
            )
            ->select($qb->expr()->count('m'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Sort threads by createdAt
     *
     * @param array $ids
     * @return array
     */
    public function searchSorted($ids)
    {
        $qb = $this->createQueryBuilder('thread');
        $qb->innerJoin('thread.metadata', 'metadata')
            ->innerJoin('thread.messages', 'm')
            ->where($qb->expr()->in('thread.id', ':ids'))
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'ids' => $ids,
            ]);
        return $qb->getQuery()->getResult();
    }

    /**
     * Get unreaded messages for a user
     *
     * @param $user
     * @return array
     */
    public function getLastMessages($user)
    {
        $qb = $this->createQueryBuilder('thread');
        $qb->innerJoin('thread.metadata', 'metadata')
            ->innerJoin('thread.messages', 'm')
            ->innerJoin('m.metadata', 'msgMeta')
            ->where($qb->expr()->eq('msgMeta.participant', ':user'))
            ->andWhere($qb->expr()->eq('msgMeta.isRead', 'false'))
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'user' => $user,
            ]);
        return $qb->getQuery()->getResult();
    }
}
