<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Production
 *
 * @ORM\Table("tj_production")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\ProductionRepository")
 */
class Production
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
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="productions")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $organizationRelated;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $year;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Creator", inversedBy="productions", cascade={"persist"})
     * @ORM\JoinTable(name="tj_production_creators")
     *      joinColumns={@ORM\JoinColumn(name="productions_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="creator_id", referencedColumnName="id")}
     *      )
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify at least one creator",
     * )
     * */
    public $creators;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Director", inversedBy="productions", cascade={"persist"})
     * @ORM\JoinTable(name="tj_production_directors")
     *      joinColumns={@ORM\JoinColumn(name="productions_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="director_id", referencedColumnName="id")}
     *      )
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify at least one director",
     * )
     * */
    public $directors;

    /**
     * @var bool
     * @ORM\Column(name="isChecked", type="boolean", nullable=false)
     */
    protected $checked = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checked_at", type="datetime", nullable=true)
     */
    private $checkedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="checked_by", referencedColumnName="id")
     */
    private $checkedBy;

    /**
     * @ORM\OneToMany(targetEntity="ProductionParticipations", mappedBy="production")
     */
    protected $participations;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->directors = new ArrayCollection();
        $this->creators = new ArrayCollection();
        $this->participations = new ArrayCollection();

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
     * Set name
     *
     * @param string $name
     *
     * @return Production
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
     * Set organizationRelated
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizationRelated
     *
     * @return Production
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

    /**
     * Set year
     *
     * @param string $year
     *
     * @return Production
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Add creator
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Creator $creators
     *
     * @return Production
     */
    public function addCreators(Creator $creators)
    {
        if (!$this->creators->contains($creators)) {
            $this->creators->add($creators);
        }

        return $this;
    }

    /**
     * Remove creator
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Creator $creators
     */
    public function removeCreators(Creator $creators)
    {
        $this->creators->removeElement($creators);
    }

    /**
     * Get creators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreators()
    {
        return $this->creators;
    }

    /**
     * Add director
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Director $directors
     *
     * @return Production
     */
    public function addDirectors(Director $directors)
    {
        if (!$this->directors->contains($directors)) {
            $this->directors->add($directors);
        }

        return $this;
    }

    /**
     * Remove director
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Director $directors
     */
    public function removeDirectors(Director $directors)
    {
        $this->directors->removeElement($directors);
    }

    /**
     * Get director
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDirectors()
    {
        return $this->directors;
    }

    /**
     * Add participation
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProductionParticipations $participation
     *
     * @return Production
     */
    public function addParticipation(ProductionParticipations $participation)
    {
        $this->participations[] = $participation;

        return $this;
    }

    /**
     * Remove participation
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProductionParticipations $participation
     */
    public function removeParticipation(ProductionParticipations $participation)
    {
        $this->participations->removeElement($participation);
    }

    /**
     * Get participations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParticipations()
    {
        return $this->participations;
    }


    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * Set checkedAt
     *
     * @param \DateTime $checkedAt
     *
     * @return Production
     */
    public function setCheckedAt($checkedAt)
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }

    /**
     * Get checkedAt
     *
     * @return \DateTime
     */
    public function getCheckedAt()
    {
        return $this->checkedAt;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     *
     * @return Production
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get checkedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * Set checkedBy
     *
     * @param Profile $checkedBy
     *
     * @return Production
     */
    public function setCheckedBy($checkedBy)
    {
        $this->checkedBy = $checkedBy;

        return $this;
    }

    /**
     * Get checkedBy
     *
     * @return integer
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

}

