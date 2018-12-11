<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserActivity
 *
 * @ORM\Table("tj_user_user_activity")
 * @ORM\Entity(repositoryClass="Theaterjobs\UserBundle\Entity\UserActivityRepository")
 */
class UserActivity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userActivity", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_user_user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="activityText", type="text")
     */
    private $activityText;

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
     * @ORM\Column(name="only_for_admin",type="boolean", nullable=false)
     */
    private $adminOnly;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="uacCreatedBy", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_user_created_by", referencedColumnName="id", nullable=true)
     */
    private $createdBy;
    
    /**
     * @var string
     *
     * @ORM\Column(name="changedFields", type="json_array", nullable=true)
     */
    private $changedFields;


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return UserActivity
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
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     * @return AdminComments
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set activityText
     *
     * @param string $activityText
     * @return UserActivity
     */
    public function setActivityText($activityText)
    {
        $this->activityText = $activityText;

        return $this;
    }

    /**
     * Get activityText
     *
     * @return string 
     */
    public function getActivityText()
    {
        return $this->activityText;
    }

    /**
     * Set entityClass
     *
     * @param string $entityClass
     * @return UserActivity
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
     * @return UserActivity
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
    
    function getAdminOnly() {
        return $this->adminOnly;
    }

    function setAdminOnly($adminOnly) {
        $this->adminOnly = $adminOnly;
    }

    function getCreatedBy() {
        return $this->createdBy;
    }

    function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
    }

    function getChangedFields() {
        return $this->changedFields;
    }

    function setChangedFields($changedFields) {
        $this->changedFields = $changedFields;
    }
}
