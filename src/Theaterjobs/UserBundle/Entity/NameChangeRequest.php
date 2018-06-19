<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NameChangeRequest
 *
 * @ORM\Table(name="tj_user_name_change_requests")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\NameChangeRequestRepository")
 */
class NameChangeRequest
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;
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
     * @ORM\Column(name="old_first_name", type="string", length=255, nullable=true)
     */
    private $oldFirstName;


    /**
     * @var string
     *
     * @ORM\Column(name="old_last_name", type="string", length=255, nullable=true)
     */
    private $oldLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="new_first_name", type="string", length=255, nullable=true)
     */
    private $newFirstName;


    /**
     * @var string
     *
     * @ORM\Column(name="new_last_name", type="string", length=255, nullable=true)
     */
    private $newLastName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetimetz", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userManagedNameChangeRequests", fetch="EAGER")
     * @ORM\JoinColumn(name="updatedBy", referencedColumnName="id", nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userNameChangeRequests", fetch="EAGER")
     * @ORM\JoinColumn(name="createdBy", referencedColumnName="id", nullable=true)
     */
    private $createdBy;

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
     * @return string
     */
    public function getOldFirstName()
    {
        return $this->oldFirstName;
    }

    /**
     * @param string $oldFirstName
     * @return NameChangeRequest
     */
    public function setOldFirstName($oldFirstName)
    {
        $this->oldFirstName = $oldFirstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldLastName()
    {
        return $this->oldLastName;
    }

    /**
     * @param mixed $oldLastName
     * @return NameChangeRequest
     */
    public function setOldLastName($oldLastName)
    {
        $this->oldLastName = $oldLastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewFirstName()
    {
        return $this->newFirstName;
    }

    /**
     * @param string $newFirstName
     * @return NameChangeRequest
     */
    public function setNewFirstName($newFirstName)
    {
        $this->newFirstName = $newFirstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewLastName()
    {
        return $this->newLastName;
    }

    /**
     * @param string $newLastName
     * @return NameChangeRequest
     */
    public function setNewLastName($newLastName)
    {
        $this->newLastName = $newLastName;
        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return NameChangeRequest
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return NameChangeRequest
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return NameChangeRequest
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param $updatedBy
     * @return $this
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return integer
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return NameChangeRequest
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }


    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}

