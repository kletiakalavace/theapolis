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
 * @ORM\Table(name="tj_inserate_organization_grants")
 * @ORM\Entity()
 */
class OrganizationGrants
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationGrants")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     *
     */
    protected $organizations;

    /**
     * @var integer
     *
     * @ORM\Column(name="budget", type="decimal",precision=20, scale=2, nullable=true)
     */
    private $budget;

    /**
     * @var integer
     *
     * @ORM\Column(name="grants", type="decimal",precision=20, scale=2, nullable=true)
     */
    private $grants;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set budget
     *
     * @param integer $budget
     *
     * @return OrganizationGrants
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return integer
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set grants
     *
     * @param integer $grants
     *
     * @return OrganizationGrants
     */
    public function setGrants($grants)
    {
        $this->grants = $grants;

        return $this;
    }

    /**
     * Get grants
     *
     * @return integer
     */
    public function getGrants()
    {
        return $this->grants;
    }

    /**
     * Set season
     *
     * @param string $season
     *
     * @return OrganizationGrants
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
     *
     * @return OrganizationGrants
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
     *
     * @return OrganizationGrants
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
