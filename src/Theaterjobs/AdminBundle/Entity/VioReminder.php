<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VioReminder
 *
 * @ORM\Table(name="tj_admin_organization_vio_reminder")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\VioReminderRepository")
 */
class VioReminder
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
     * One vioReminder has One vio.
     * @ORM\OneToOne(targetEntity="Vio", inversedBy="vioReminder")
     */
    private $vio;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
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
     * @return mixed
     */
    public function getVio()
    {
        return $this->vio;
    }

    /**
     * @param mixed $vio
     * @return VioReminder
     */
    public function setVio($vio)
    {
        $this->vio = $vio;
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
     * @return VioReminder
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}

