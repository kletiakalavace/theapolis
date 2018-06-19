<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the organization.
 *
 * @ORM\Table(name="tj_inserate_organization_visitors")
 * @ORM\Entity()
 */
class OrganizationVisitors
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationVisitors")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     *
     */
    protected $organizations;

    /**
     *
     * @var type integer
     * @ORM\Column(name="visitors_number", type="decimal",precision=50, nullable=true)
     */
    protected $visitorsNumber;

    /**
     *
     * @var type string
     * @ORM\Column(name="season", type="string",length=255)
     */
    protected $season;

    /**
     * @var string
     *
     * @ORM\Column(name="more_infor", type="text", nullable=true)
     */
    protected $moreInfo;

    /**
     * Constructor
     */
    public function __construct()
    {

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
     * Set visitorsNumber
     *
     * @param integer $visitorsNumber
     * @return OrganizationVisitors
     */
    public function setVisitorsNumber($visitorsNumber)
    {
        $this->visitorsNumber = $visitorsNumber;

        return $this;
    }

    /**
     * Get visitorsNumber
     *
     * @return integer
     */
    public function getVisitorsNumber()
    {
        return $this->visitorsNumber;
    }

    /**
     * Set season
     *
     * @param string $season
     * @return OrganizationVisitors
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set moreInfo
     *
     * @param string $moreInfo
     * @return OrganizationVisitors
     */
    public function setMoreInfo($moreInfo)
    {
        $this->moreInfo = $moreInfo;

        return $this;
    }

    /**
     * Get moreInfo
     *
     * @return string
     */
    public function getMoreInfo()
    {
        return $this->moreInfo;
    }


    /**
     * Set organizations
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizations
     * @return OrganizationVisitors
     */
    public function setOrganizations(\Theaterjobs\InserateBundle\Entity\Organization $organizations = null)
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Get organizations
     *
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }
}
