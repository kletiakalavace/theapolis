<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Topics
 *
 * @ORM\Table(name="tj_notifications_type_of_notifications")
 * @ORM\Entity
 */
class TypeOfNotification {

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
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
    * @var string
    * @ORM\Column(name="code", type="string", length=255, nullable=true)
    */
    protected $code;

    /**
     * @var boolean
     * @ORM\Column(name="require_action", type="boolean")
     */
    protected $requireAction;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Notification",
     *     mappedBy="typeOfNotification",
     *     cascade={"persist"}
     * )
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(
     *     targetEntity="NotificationSettings",
     *     mappedBy="typeOfNotification",
     *     cascade={"persist"}
     * )
     */
    protected $notificationSettings;


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
     * @return TypeOfNotification
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
     * Set requireAction
     *
     * @param boolean $requireAction
     * @return TypeOfNotification
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
     * Constructor
     */
    public function __construct()
    {
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notificationSettings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add notifications
     *
     * @param \Theaterjobs\UserBundle\Entity\Notification $notifications
     * @return TypeOfNotification
     */
    public function addNotification(\Theaterjobs\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Theaterjobs\UserBundle\Entity\Notification $notifications
     */
    public function removeNotification(\Theaterjobs\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add notificationSettings
     *
     * @param \Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings
     * @return TypeOfNotification
     */
    public function addNotificationSetting(\Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings)
    {
        $this->notificationSettings[] = $notificationSettings;

        return $this;
    }

    /**
     * Remove notificationSettings
     *
     * @param \Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings
     */
    public function removeNotificationSetting(\Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings)
    {
        $this->notificationSettings->removeElement($notificationSettings);
    }

    /**
     * Get notificationSettings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotificationSettings()
    {
        return $this->notificationSettings;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return TypeOfNotification
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
