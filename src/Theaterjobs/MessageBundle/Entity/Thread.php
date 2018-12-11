<?php

namespace Theaterjobs\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\MessageBundle\Entity\Thread as BaseThread;
use FOS\MessageBundle\Model\ParticipantInterface;
use GuzzleHttp\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_messages_thread")
 * @ORM\Entity(repositoryClass="Theaterjobs\MessageBundle\Entity\ThreadRepository")
 */
class Thread extends BaseThread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User")
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $createdBy;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Message", mappedBy="thread" )
     * @var Message[]|\Doctrine\Common\Collections\Collection
     */
    protected $messages;

    /**
     * @ORM\OneToMany(
     *   targetEntity="ThreadMetadata", mappedBy="thread", cascade={"all"})
     * @var ThreadMetadata[]|\Doctrine\Common\Collections\Collection
     */
    protected $metadata;

    /**
     * If thread is deleted
     *
     * @ORM\Column(name="close_comunication", type="boolean")
     **/
    protected $closeComunication = false;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->metadata = new ArrayCollection();
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
     * Set createdBy
     *
     * @param \Theaterjobs\UserBundle\Entity\User $createdBy
     * @return Thread
     */
    public function setCreatedBy(ParticipantInterface $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Add messages
     *
     * @param \Theaterjobs\MessageBundle\Entity\Message $messages
     * @return Thread
     */
    public function addMessage(\FOS\MessageBundle\Model\MessageInterface $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Theaterjobs\MessageBundle\Entity\Message $messages
     */
    public function removeMessage(\Theaterjobs\MessageBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add metadata
     *
     * @param \Theaterjobs\MessageBundle\Entity\ThreadMetadata $metadata
     * @return Thread
     */
    public function addMetadatum(\Theaterjobs\MessageBundle\Entity\ThreadMetadata $metadata)
    {
        $this->metadata[] = $metadata;

        return $this;
    }

    /**
     * Remove metadata
     *
     * @param \Theaterjobs\MessageBundle\Entity\ThreadMetadata $metadata
     */
    public function removeMetadatum(\Theaterjobs\MessageBundle\Entity\ThreadMetadata $metadata)
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
     * @return bool
     */
    function getCloseComunication()
    {
        return $this->closeComunication;
    }

    /**
     * @param $closeComunication
     */
    function setCloseComunication($closeComunication)
    {
        $this->closeComunication = $closeComunication;
    }

}
