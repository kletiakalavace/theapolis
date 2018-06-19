<?php

namespace Theaterjobs\MessageBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Class MessageRepository
 * @package Theaterjobs\MessageBundle\Entity
 */
class MessageRepository extends EntityRepository
{
    /**
     * Get messages of a thread
     *
     * @param Thread $thread
     * @param User $participant
     * @return \Doctrine\ORM\Query
     */
    public function threadMessages($thread, $participant)
    {
        //If user has deleted conversation once
        if (!$metaData = $thread->getMetadataForParticipant($participant)) {
            throw new \InvalidArgumentException(sprintf('No metadata exists for participant with id "%s"', $participant->getId()));
        }

        $messages = $this->createQueryBuilder('msg')
            ->innerJoin('msg.thread', 'th')
            ->innerJoin('th.metadata', 'thMeta')
            ->where('th.id = :threadID')
            ->andWhere('thMeta.participant = :participant')
            ->orderBy('msg.createdAt', 'DESC')
                ->setParameters(array(
                    'threadID' => $thread->getId(),
                    'participant' => $participant
                ));

        return $messages->getQuery();
    }

    /**
     * Paginate messages
     *
     * @param Thread $thread
     * @param User $participant
     * @param int $lastMsgID
     *
     * @return \Doctrine\ORM\Query
     */
    public function paginateMessages($thread, $participant, $lastMsgID = 0)
    {
        //If user has deleted conversation once
        if (!$metaData = $thread->getMetadataForParticipant($participant)) {
            throw new \InvalidArgumentException(sprintf('No metadata exists for participant with id "%s"', $participant->getId()));
        }

        $messages = $this->createQueryBuilder('msg')
            ->innerJoin('msg.thread', 'th')
            ->innerJoin('th.metadata', 'thMeta')
            ->where('th.id = :threadID')
            ->andWhere('thMeta.participant = :participant')
            ->andWhere('msg.id < :lastMsgID')
            ->orderBy('msg.createdAt', 'DESC')
                ->setParameters(array(
                    'lastMsgID' => $lastMsgID,
                    'threadID' => $thread->getId(),
                    'participant' => $participant,
                ));

        return $messages->getQuery();
    }

    /**
     * Returns last message of a thread
     *
     * @param $thId
     * @return array
     */
    public function lastThreadMessage($thId)
    {
        $query = $this->createQueryBuilder('message')
            ->innerJoin('message.thread', 'th')
            ->where('th.id = :thId')
            ->orderBy('message.createdAt', 'DESC')
            ->setMaxResults(1)
        ->setParameter('thId', $thId);
        return $query->getQuery()->getResult();
    }

}