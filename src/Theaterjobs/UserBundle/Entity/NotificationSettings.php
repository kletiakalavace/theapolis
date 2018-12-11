<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Topics
 *
 * @ORM\Table(name="tj_notifications_settings")
 * @ORM\Entity
 */
class NotificationSettings {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="TypeOfNotification", inversedBy="notificationSettings", fetch="EAGER")
     * @ORM\JoinColumn(name="notification_type_id", referencedColumnName="id", nullable=true)
     */
    protected $typeOfNotification;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notificationSettings", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

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
     * Set typeOfNotification
     *
     * @param \Theaterjobs\UserBundle\Entity\TypeOfNotification $typeOfNotification
     * @return NotificationSettings
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
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     * @return NotificationSettings
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
}
