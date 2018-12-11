<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Organization
 *
 * @ORM\Table("tj_profile_organization")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\OrganizationRepository")
 */
class Organization
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archivedAt", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="profileOrganization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     */
    private $organizationRelated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="organization_revoked", type="boolean")
     */
    private $organizationRevoked = false;

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
     * Set name
     *
     * @param string $name
     *
     * @return Organization
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     *
     * @return Organization
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * Set organizationRevoked
     *
     * @param boolean $organizationRevoked
     *
     * @return Organization
     */
    public function setOrganizationRevoked($organizationRevoked)
    {
        $this->organizationRevoked = $organizationRevoked;

        return $this;
    }

    /**
     * Get organizationRevoked
     *
     * @return boolean
     */
    public function getOrganizationRevoked()
    {
        return $this->organizationRevoked;
    }

    /**
     * Set organizationRelated
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizationRelated
     *
     * @return Organization
     */
    public function setOrganizationRelated(\Theaterjobs\InserateBundle\Entity\Organization $organizationRelated = null)
    {
        $this->organizationRelated = $organizationRelated;

        return $this;
    }

    /**
     * Get organizationRelated
     *
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     */
    public function getOrganizationRelated()
    {
        return $this->organizationRelated;
    }
}
