<?php

namespace Theaterjobs\MessageBundle\Twig;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Exception\Exception;
use Theaterjobs\MessageBundle\Entity\Message;
use Theaterjobs\MessageBundle\Entity\Thread;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Twig extension that contains all function used on messages
 *
 * Class MessagesExtension
 * @package Theaterjobs\MessageBundle\Twig
 */
class MessagesExtension extends \Twig_Extension
{
    /** @var EntityManager $em */
    private $em;

    /**
     * NrUnreadMessages constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_message_seen', array($this, 'isMessageSeenFunction')),
            new \Twig_SimpleFunction('last_thread_msg', array($this, 'lastThreadMessageFunction')),
            new \Twig_SimpleFunction('nr_unread_msgs', array($this, 'nrUnreadMsgFunction')),
            new \Twig_SimpleFunction('thread_receipt', array($this, 'threadReceiptFunction')),
            new \Twig_SimpleFunction('last_messages', array($this, 'lastMessagesFunction')),
        );
    }

    /**
     * Checks if a message is seen or not by a user
     *
     * @param Message $message
     * @param User $user
     * @throws Exception
     *
     * @return boolean
     */
    public function isMessageSeenFunction($message, $user)
    {
        $metadata = $message->getMetadata();
        if(!$metadata || count($metadata) == 0) {
            throw new Exception("Not found message metadata");
        }
        foreach ($metadata as $meta) {
            if ($meta->getParticipant() != $user) {
                return $meta->getIsRead();
            }
        }
        return false;
    }

    /**
     * Get last sent message from a thread
     *
     * @param Thread $thread
     *
     * @return boolean | Message
     */
    public function lastThreadMessageFunction(Thread $thread)
    {
        $message = $this->em->getRepository(Message::class)->lastThreadMessage($thread->getId());
        return $message[0];
    }

    /**
     * Get nr of unread messages from a thread
     *
     * @param Thread $thread
     * @param User $user
     * @throws Exception
     *
     * @return boolean
     */
    public function nrUnreadMsgFunction($thread, $user)
    {
        return $this->em->getRepository(Thread::class)
            ->getUnreadMessages($thread, $user)[0][1];
    }

    /**
     * Get receipt of a thread with 2 participants
     *
     * @param Thread $thread
     *
     * @return User
     */
    public function threadReceiptFunction($thread, $user)
    {
        $participants = $thread->getParticipants();
        if (count($participants) == 1) {
            return $participants[0];
        }
        return ($user == $participants[0]) ? $participants[1] : $participants[0];
    }

    /**
     * Returns last unreaded messages for dashboard
     * @param $user
     * @return array
     */
    public function lastMessagesFunction($user)
    {
        return $this->em->getRepository(Thread::class)->getLastMessages($user);

    }
}