<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\UserBundle\Entity\User;

/**
 * TeamMembershipApplication
 *
 * @ORM\Table(name="tj_inserate_organization_team_membership_application")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\TeamMembershipApplicationRepository")
 */
class TeamMembershipApplication
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
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", inversedBy="membershipApplications")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="membershipApplications")
     */
    private $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="applicationText", type="text")
     */
    private $applicationText;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pending", type="boolean")
     */
    private $pending = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;


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
     * Set user
     *
     * @param integer $user
     *
     * @return TeamMembershipApplication
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set organization
     *
     * @param integer $organization
     *
     * @return TeamMembershipApplication
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set applicationText
     *
     * @param string $applicationText
     *
     * @return TeamMembershipApplication
     */
    public function setApplicationText($applicationText)
    {
        $this->applicationText = $applicationText;

        return $this;
    }

    /**
     * Get applicationText
     *
     * @return string
     */
    public function getApplicationText()
    {
        return $this->applicationText;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TeamMembershipApplication
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
     * @return bool
     */
    public function isApproved()
    {
        return $this->pending;
    }

    /**
     * @param bool $pending
     * @return TeamMembershipApplication
     */
    public function setApproved($pending)
    {
        $this->pending = $pending;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->pending;
    }

    /**
     * @param bool $pending
     * @return TeamMembershipApplication
     */
    public function setPending($pending)
    {
        $this->pending = $pending;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return TeamMembershipApplication
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }


}

