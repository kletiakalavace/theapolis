<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrganizationEnsemble
 *
 * @ORM\Table(name="tj_inserate_organizations_staff")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\OrganizationStaffRepository")
 */
class OrganizationStaff
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="number", type="integer")
     * */
    private $groupNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationStaff", cascade={"persist"})
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * 
     */
    private $organization;


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
     * @return OrganizationEnsemble
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
     * Set organization
     *
     * @param integer $organization
     *
     * @return OrganizationEnsemble
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return integer
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set groupNumber
     *
     * @param integer $groupNumber
     *
     * @return OrganizationStaff
     */
    public function setGroupNumber($groupNumber)
    {
        $this->groupNumber = $groupNumber;

        return $this;
    }

    /**
     * Get groupNumber
     *
     * @return integer
     */
    public function getGroupNumber()
    {
        return $this->groupNumber;
    }
}
