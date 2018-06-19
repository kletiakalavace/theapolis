<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Experience
 *
 * @ORM\Table("tj_profile_experience")
 * @ORM\Entity()
 */
class Experience
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
     * @ORM\ManyToOne( targetEntity="Profile", inversedBy="experience" )
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

  /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="experiences")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $organization;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface")
     * @Assert\NotNull()
     */
    private $occupation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="assistant", type="boolean", nullable=true)
     */
    private $assistant = false;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="management", type="boolean", nullable=true)
     */
    private $management =false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime",  nullable=true)
     */
    private $end;

    /**
    * @var boolean
    *
    * @ORM\Column(name="ongoing", type="boolean", nullable=true)
    */
    private $ongoing = false;

    /**
     * @var string
     * @ORM\Column(name="used_name", type="string", nullable=true)
     */
    protected $usedName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="used_name_check", type="boolean", nullable=true)
     */
    private $usedNameCheck =false;

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
     * @param $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set occupation
     *
     * @param string $occupation
     *
     * @return Production
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set managementResponsibility
     *
     * @param boolean $management
     *
     * @return Experience
     */
    public function setManagement($management)
    {
        $this->management = $management;

        return $this;
    }

    /**
     * Get managementResponsibility
     *
     * @return boolean
     */
    public function getManagement()
    {
        return $this->management;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return Experience
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     *
     * @return Experience
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set organization
     *
     * @param integer $organization
     *
     * @return Experience
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
     * Set assistant
     *
     * @param boolean $assistant
     *
     * @return Experience
     */
    public function setAssistant($assistant)
    {
        $this->assistant = $assistant;

        return $this;
    }

    /**
     * Get assistant
     *
     * @return boolean
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Experience
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ongoing
     *
     * @param boolean $ongoing
     *
     * @return Experience
     */
    public function setOngoing($ongoing)
    {
        $this->ongoing = $ongoing;
        return $this;
    }
    /**
     * Get ongoing
     *
     * @return boolean
     */
    public function getOngoing()
    {
        return $this->ongoing;
    }

    /**
     * @return string
     */
    public function getUsedName()
    {
        return $this->usedName;
    }

    /**
     * @param string $usedName
     */
    public function setUsedName($usedName)
    {
        $this->usedName = $usedName;
    }

    /**
     * @return bool
     */
    public function isUsedNameCheck()
    {
        return $this->usedNameCheck;
    }

    /**
     * @param bool $usedNameCheck
     */
    public function setUsedNameCheck($usedNameCheck)
    {
        $this->usedNameCheck = $usedNameCheck;
    }

}

