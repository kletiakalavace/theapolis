<?php

namespace Theaterjobs\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Entity\MessageMetadata as BaseMessageMetadata;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_messages_message_metadata")
 * @ORM\Entity(repositoryClass="Theaterjobs\MessageBundle\Entity\MessageMetadataRepository")
 */
class MessageMetadata extends BaseMessageMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Message", inversedBy="metadata")
     * @var \FOS\MessageBundle\Model\MessageInterface
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User")
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $participant;

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
     * Set message
     *
     * @param \FOS\MessageBundle\Model\MessageInterface|Message $message
     * @return MessageMetadata
     */
    public function setMessage(\FOS\MessageBundle\Model\MessageInterface $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Theaterjobs\MessageBundle\Entity\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set participant
     *
     * @param \FOS\MessageBundle\Model\ParticipantInterface|\Theaterjobs\UserBundle\Entity\User $participant
     * @return MessageMetadata
     */
    public function setParticipant(\FOS\MessageBundle\Model\ParticipantInterface $participant = null)
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * Get participant
     *
     * @return \Theaterjobs\UserBundle\Entity\User 
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
