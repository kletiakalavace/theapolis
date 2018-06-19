<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductionParticipations
 *
 * @ORM\Table("tj_production_participations")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\ProductionParticipationsRepository")
 */
class ProductionParticipations
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
     * @ORM\ManyToOne( targetEntity="Profile", inversedBy="productionParticipations" )
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @ORM\ManyToOne( targetEntity="Theaterjobs\ProfileBundle\Entity\Production", inversedBy="participations", cascade={"persist"})
     * @ORM\JoinColumn(name="production_id", referencedColumnName="id")
     */
    protected $production;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface")
     * @Assert\NotNull()
     */
    private $occupation;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Occupation", inversedBy="production", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="occupation_description_id", referencedColumnName="id")
     * */
    protected $occupationDescription;

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
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return ProductionParticipations
     */
    public function setProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set production
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Production $production
     *
     * @return ProductionParticipations
     */
    public function setProduction(Production $production)
    {
        $this->production = $production;

        return $this;
    }

    /**
     * Get production
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Production
     */
    public function getProduction()
    {
        return $this->production;
    }

    /**
     * Set occupation
     *
     * @param string $occupation
     *
     * @return ProductionParticipations
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
     * Set occupationDescription
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Occupation $occupationDescription
     *
     * @return ProductionParticipations
     */
    public function setOccupationDescription(\Theaterjobs\ProfileBundle\Entity\Occupation $occupationDescription = null)
    {
        $this->occupationDescription = $occupationDescription;

        return $this;
    }

    /**
     * Get occupationDescription
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Occupation
     */
    public function getOccupationDescription()
    {
        return $this->occupationDescription;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Set ongoing
     *
     * @param boolean $ongoing
     *
     * @return ProductionParticipations
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

