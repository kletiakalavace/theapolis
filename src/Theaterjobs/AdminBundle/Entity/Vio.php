<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Vio
 *
 * @ORM\Table(name="tj_admin_organization_vio")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\VioRepository")
 */
class Vio
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
     * @var integer
     *
     * @ORM\Column(name="daysInterval", type="integer")
     */
    private $daysInterval = 30;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_checked", type="boolean")
     */
    private $isChecked = false;

    /**
     * One vio has One organization.
     * @ORM\OneToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="vio")
     */
    private $organization;

    /**
     * One vio has One organization.
     * @ORM\OneToOne(targetEntity="VioReminder", mappedBy="vio", cascade={"remove"})
     */
    private $vioReminder;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

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
     * Set daysInterval
     *
     * @param integer $daysInterval
     *
     * @return Vio
     */
    public function setDaysInterval($daysInterval)
    {
        $this->daysInterval = $daysInterval;

        return $this;
    }

    /**
     * Get daysInterval
     *
     * @return integer
     */
    public function getDaysInterval()
    {
        return $this->daysInterval;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     * @return Vio
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Vio
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Vio
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getisChecked()
    {
        return $this->isChecked;
    }

    /**
     * @param int $isChecked
     * @return Vio
     */
    public function setIsChecked($isChecked)
    {
        $this->isChecked = $isChecked;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVioReminder()
    {
        return $this->vioReminder;
    }

    /**
     * @param mixed $vioReminder
     * @return Vio
     */
    public function setVioReminder($vioReminder)
    {
        $this->vioReminder = $vioReminder;
        return $this;
    }
}

