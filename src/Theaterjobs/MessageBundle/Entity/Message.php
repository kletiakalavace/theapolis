<?php

namespace Theaterjobs\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\MessageBundle\Entity\Message as BaseMessage;
use Theaterjobs\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_messages_message")
 * @ORM\Entity(repositoryClass="Theaterjobs\MessageBundle\Entity\MessageRepository")
 */
class Message extends BaseMessage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Thread", inversedBy="messages")
     * @var \FOS\MessageBundle\Model\ThreadInterface
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User")
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $sender;

    /**
     * @ORM\OneToMany(
     *   targetEntity="MessageMetadata", mappedBy="message", cascade={"all"})
     * @var MessageMetadata
     */
    protected $metadata;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User")
     * @var User
     */
    protected $deletedBy;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->metadata = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set thread
     *
     * @param \Theaterjobs\MessageBundle\Entity\Thread $thread
     * @return Message
     */
    public function setThread(\FOS\MessageBundle\Model\ThreadInterface $thread = null)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return \Theaterjobs\MessageBundle\Entity\Thread 
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set sender
     *
     * @param \Theaterjobs\UserBundle\Entity\User $sender
     * @return Message
     */
    public function setSender(\FOS\MessageBundle\Model\ParticipantInterface $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \Theaterjobs\UserBundle\Entity\User 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Add metadata
     *
     * @param \Theaterjobs\MessageBundle\Entity\MessageMetadata $metadata
     * @return Message
     */
    public function addMetadatum(\Theaterjobs\MessageBundle\Entity\MessageMetadata $metadata)
    {
        $this->metadata[] = $metadata;

        return $this;
    }

    /**
     * Remove metadata
     *
     * @param \Theaterjobs\MessageBundle\Entity\MessageMetadata $metadata
     */
    public function removeMetadatum(\Theaterjobs\MessageBundle\Entity\MessageMetadata $metadata)
    {
        $this->metadata->removeElement($metadata);
    }

    /**
     * Get metadata
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return User | null
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * @param User $deletedBy
     */
    public function setDeletedBy($deletedBy)
    {
        $this->deletedBy = $deletedBy;
    }

}
