<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * OrganizationStage
 *
 * @ORM\Table(name="tj_inserate_organizations_stage")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\OrganizationStageRepository")
 */
class OrganizationStage
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
     * @ORM\Column(name="stageTitle", type="text")
     */
    private $stageTitle;

    /**
     * @var integer
     * @ORM\Column(name="seats", type="integer", nullable=true)
     */
    protected $stageSeats;

    /**
     * @var float
     * @ORM\Column(name="stageWidth", type="float", nullable=true)
     */
    private $stageWidth;

    /**
     * @var float
     * @ORM\Column(name="stageDepth", type="float", nullable=true)
     */
    private $stageDepth;

    /**
     * @var float
     * @ORM\Column(name="portalWidth", type="float", nullable=true)
     */
    private $portalWidth;

    /**
     * @var float
     * @ORM\Column(name="portalDepth", type="float", nullable=true)
     */
    private $portalDepth;

    /**
     * @var integer
     * @ORM\Column(name="hubStages", type="integer", nullable=true)
     */
    private $hubStages;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Entity\Tags", inversedBy="organizationStage", cascade={"persist"})
     * @ORM\JoinTable(name="tj_organization_stage_tags")
     * */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationStage")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     *
     */
    protected $organizations;

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
        $this->tags = new ArrayCollection();
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
     * Set stageTitle
     *
     * @param string $stageTitle
     *
     * @return OrganizationStage
     */
    public function setStageTitle($stageTitle)
    {
        $this->stageTitle = $stageTitle;

        return $this;
    }

    /**
     * Get stageTitle
     *
     * @return string
     */
    public function getStageTitle()
    {
        return $this->stageTitle;
    }

    /**
     * Set seats
     *
     * @param integer $stageSeats
     *
     * @return OrganizationStage
     */
    public function setStageSeats($stageSeats)
    {
        $this->stageSeats = $stageSeats;

        return $this;
    }

    /**
     * Get seats
     *
     * @return integer
     */
    public function getStageSeats()
    {
        return $this->stageSeats;
    }

    /**
     * Set stageWidth
     *
     * @param float $stageWidth
     *
     * @return OrganizationStage
     */
    public function setStageWidth($stageWidth)
    {
        $this->stageWidth = $stageWidth;

        return $this;
    }

    /**
     * Get stageWidth
     *
     * @return float
     */
    public function getStageWidth()
    {
        return $this->stageWidth;
    }

    /**
     * Set stageDepth
     *
     * @param float $stageDepth
     *
     * @return OrganizationStage
     */
    public function setStageDepth($stageDepth)
    {
        $this->stageDepth = $stageDepth;

        return $this;
    }

    /**
     * Get stageDepth
     *
     * @return float
     */
    public function getStageDepth()
    {
        return $this->stageDepth;
    }

    /**
     * Set portalWidth
     *
     * @param float $portalWidth
     *
     * @return OrganizationStage
     */
    public function setPortalWidth($portalWidth)
    {
        $this->portalWidth = $portalWidth;

        return $this;
    }

    /**
     * Get portalWidth
     *
     * @return float
     */
    public function getPortalWidth()
    {
        return $this->portalWidth;
    }

    /**
     * Set portalDepth
     *
     * @param float $portalDepth
     *
     * @return OrganizationStage
     */
    public function setPortalDepth($portalDepth)
    {
        $this->portalDepth = $portalDepth;

        return $this;
    }

    /**
     * Get portalDepth
     *
     * @return float
     */
    public function getPortalDepth()
    {
        return $this->portalDepth;
    }

    /**
     * Set hubStages
     *
     * @param integer $hubStages
     *
     * @return OrganizationStage
     */
    public function setHubStages($hubStages)
    {
        $this->hubStages = $hubStages;

        return $this;
    }

    /**
     * Get hubStages
     *
     * @return integer
     */
    public function getHubStages()
    {
        return $this->hubStages;
    }

    /**
     * Add tags
     *
     * @param \Theaterjobs\InserateBundle\Entity\Tags $tag
     * @return OrganizationStage
     */
    public function addTag(\Theaterjobs\InserateBundle\Entity\Tags $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        
        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Theaterjobs\InserateBundle\Entity\Tags $tag
     */
    public function removeTag(\Theaterjobs\InserateBundle\Entity\Tags $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    function getOrganizations()
    {
        return $this->organizations;
    }

    function setOrganizations($organizations)
    {
        $this->organizations = $organizations;
    }

    /**
     * Set moreInfo
     *
     * @param string $moreInfo
     *
     * @return OrganizationStage
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


}

