<?php

namespace Theaterjobs\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Entity\ThreadMetadata as BaseThreadMetadata;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_messages_thread_metadata")
 * @ORM\Entity(repositoryClass="Theaterjobs\MessageBundle\Entity\ThreadMetadataRepository")
 */
class ThreadMetadata extends BaseThreadMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Thread",
     *   inversedBy="metadata"
     * )
     * @var \FOS\MessageBundle\Model\ThreadInterface
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", inversedBy="metadataThreads")
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $participant;

    public function __construct() {
      //  parent::__construct();
        $this->setLastMessageDate(new \DateTime());
        $this->setLastParticipantMessageDate(new \DateTime());
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
     * @return ThreadMetadata
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
     * Set participant
     *
     * @param \Theaterjobs\UserBundle\Entity\User $participant
     * @return ThreadMetadata
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