<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Topics
 *
 * @ORM\Table(name="tj_notifications_notifications")
 * @ORM\Entity(repositoryClass="Theaterjobs\UserBundle\Entity\NotificationRepository")
 * @ORM\EntityListeners({"Theaterjobs\UserBundle\EventListener\NotificationListener"})
 */
class Notification {

    const NR_NRA = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=300)
     */
    protected $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=300)
     */
    protected $link;
    
     /**
     * @ORM\ManyToOne(targetEntity="TypeOfNotification", inversedBy="notifications", fetch="EAGER")
     * @ORM\JoinColumn(name="notification_type_id", referencedColumnName="id", nullable=true)
     */
    protected $typeOfNotification;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notificationsFrom", fetch="EAGER")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", nullable=true)
     */
    protected $from;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notifications", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;


    /**
     * @var boolean
     * @ORM\Column(name="seen", type="boolean")
     */
    protected $seen;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;
    
    /**
     * @var boolean
     * @ORM\Column(name="require_action", type="boolean")
     */
    protected $requireAction = false;
        
    /**
     * @var string
     *
     * @ORM\Column(name="entityClass", type="string", length=255)
     */
    private $entityClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="entityId", type="integer")
     */
    private $entityId;
    
    /**
     * @var boolean
     * @ORM\Column(name="is_message", type="boolean")
     */
    protected $isMessage = false;

    /**
     * @var array
     *
     * @ORM\Column(name="translation_keys", type="json_array", nullable=true)
     */
    private $translationKeys = null;

    /**
     * @var array
     *
     * @ORM\Column(name="translation_desc_keys", type="json_array", nullable=true)
     */
    private $translationDescKeys = null;

    /**
     * @return array
     */
    public function getTranslationDescKeys()
    {
        return $this->translationDescKeys;
    }

    /**
     * @param array $translationDescKeys
     *
     * @return Notification
     */
    public function setTranslationDescKeys($translationDescKeys)
    {
        $this->translationDescKeys = $translationDescKeys;

        return $this;
    }
    /**
     * @var array
     *
     * @ORM\Column(name="link_keys", type="json_array", nullable=true)
     */
    private $linkKeys = null;

    /**
     * @return array
     */
    public function getLinkKeys()
    {
        return $this->linkKeys;
    }

    /**
     * @param array $linkKeys
     *
     * @return Notification
     */
    public function setLinkKeys($linkKeys)
    {
        $this->linkKeys = $linkKeys;

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslationKeys()
    {
        return $this->translationKeys;
    }

    /**
     * @param array $translationKeys
     *
     * @return  Notification
     */
    public function setTranslationKeys($translationKeys)
    {
        $this->translationKeys = $translationKeys;

        return $this;
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
     * Set title
     *
     * @param string $title
     *
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Notification
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Notification
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set seen
     *
     * @param boolean $seen
     *
     * @return Notification
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Get seen
     *
     * @return boolean
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Notification
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set requireAction
     *
     * @param boolean $requireAction
     *
     * @return Notification
     */
    public function setRequireAction($requireAction)
    {
        $this->requireAction = $requireAction;

        return $this;
    }

    /**
     * Get requireAction
     *
     * @return boolean
     */
    public function getRequireAction()
    {
        return $this->requireAction;
    }

    /**
     * Set entityClass
     *
     * @param string $entityClass
     *
     * @return Notification
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entityClass
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     *
     * @return Notification
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set typeOfNotification
     *
     * @param \Theaterjobs\UserBundle\Entity\TypeOfNotification $typeOfNotification
     *
     * @return Notification
     */
    public function setTypeOfNotification(\Theaterjobs\UserBundle\Entity\TypeOfNotification $typeOfNotification = null)
    {
        $this->typeOfNotification = $typeOfNotification;

        return $this;
    }

    /**
     * Get typeOfNotification
     *
     * @return \Theaterjobs\UserBundle\Entity\TypeOfNotification
     */
    public function getTypeOfNotification()
    {
        return $this->typeOfNotification;
    }

    /**
     * Set from
     *
     * @param \Theaterjobs\UserBundle\Entity\User $from
     *
     * @return Notification
     */
    public function setFrom(\Theaterjobs\UserBundle\Entity\User $from = null)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     *
     * @return Notification
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set isMessage
     *
     * @param boolean $isMessage
     *
     * @return Notification
     */
    public function setIsMessage($isMessage)
    {
        $this->isMessage = $isMessage;

        return $this;
    }

    /**
     * Get isMessage
     *
     * @return boolean
     */
    public function getIsMessage()
    {
        return $this->isMessage;
    }


}
