<?php

namespace Theaterjobs\MessageBundle\Entity;


use Doctrine\ORM\EntityRepository;

/**
 * Class MessageMetadataRepository
 * @package Theaterjobs\MessageBundle\Entity
 */
class MessageMetadataRepository extends EntityRepository
{
    /**
     * Mark as read all messages of a thread for a specified user
     *
     * @param number $thread
     * @param number $user
     *
     * @return integer | array
     */
    public function readMessages($thread, $user)
    {
        //TEMPORARY
        //TO BE IMPROVED
        $qbd = $this->getEntityManager()->createQueryBuilder();
        $msgids = $qbd->select('mMeta.id')
            ->from('TheaterjobsMessageBundle:MessageMetadata', 'mMeta')
            ->innerJoin('mMeta.message', 'm')
            ->innerJoin('m.thread', 'th')
            ->where('mMeta.participant = :user')
            ->andWhere('th = :thread')
            ->setParameters(
                array(
                    "user" => $user,
                    "thread" => $thread
                )
            )->getQuery()->getResult();

        if (count($msgids)) {
            $readedMessages = $qbd->update( 'TheaterjobsMessageBundle:MessageMetadata', 'mMeta')
                ->set('mMeta.isRead', true)
                ->where('mMeta.id IN (:ids)')
                ->setParameters(
                    array(
                        "ids" => $msgids,
                    )
                )
                ->getQuery()->getResult();
            return $readedMessages;
        }
        return 0;
    }
}