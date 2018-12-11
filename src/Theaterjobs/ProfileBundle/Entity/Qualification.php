<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Model\QualificationInterface as InserateQualification;
use Theaterjobs\ProfileBundle\Model\JobInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\Carbon;

/**
 * Description of HairColor
 *
 * @author Malvin Dake
 * @author Vilson Duka
 * @ORM\Table(name="tj_profile_qualifications")
 * @ORM\Entity()
 */
class Qualification implements InserateQualification
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @var string
     * @ORM\Column(name="education_type", type="string", length=128, nullable=false)
     */
    protected $educationtype;

    /**
     * @var integer
     * @ORM\Column(name="startDate",type="integer", nullable=true)
     */
    protected $startDate;

    /**
     * @var boolean
     * @ORM\Column(name="finished",type="boolean", nullable=true)
     * @Assert\Expression(
     *     "this.checkDate(value) == true",
     *  message="End date must not be in the future."
     * )
     *
     */
    protected $finished;

    /**
     * @var string
     * @ORM\Column(name="experience", type="string", length=128, nullable=true)
     */
    protected $experience;

    /**
     * @var integer
     * @ORM\Column(name="endDate",type="integer", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="qualifications")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $organizationRelated;

    /**
     * @ORM\ManyToOne(targetEntity="QualificationSection", inversedBy="qualifications")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
     *
     */
    protected $qualificationSection;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=255, nullable=true)
     */
    protected $profession;

    /**
     * @var boolean
     * @ORM\Column(name="managment_responsibility",type="boolean", nullable=true)
     */
    protected $managmentResponsibility;

    /**
     * @var boolean
     * @ORM\Column(name="qualification_choice",type="boolean", nullable=true)
     */
    protected $qualificationChoice = false;

    /**
     * @var boolean
     * @ORM\Column(name="education_choice",type="boolean", nullable=true)
     */
    protected $educationChoice = false;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Model\CategoryInterface")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     */
    protected $categories;

    public function getJob()
    {

    }

    public function setJob(JobInterface $job)
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
     * Set educationtype
     *
     * @param string $educationtype
     *
     * @return Qualification
     */
    public function setEducationtype($educationtype)
    {
        $this->educationtype = $educationtype;

        return $this;
    }

    /**
     * Get educationtype
     *
     * @return string
     */
    public function getEducationtype()
    {
        return $this->educationtype;
    }


    /**
     * Set startDate
     *
     * @param integer $startDate
     *
     * @return Qualification
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return integer
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set finished
     *
     * @param boolean $finished
     *
     * @return Qualification
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished
     *
     * @return boolean
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * Set experience
     *
     * @param string $experience
     *
     * @return Qualification
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return string
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * Set endDate
     *
     * @param integer $endDate
     *
     * @return Qualification
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return integer
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set managmentResponsibility
     *
     * @param boolean $managmentResponsibility
     *
     * @return Qualification
     */
    public function setManagmentResponsibility($managmentResponsibility)
    {
        $this->managmentResponsibility = $managmentResponsibility;

        return $this;
    }

    /**
     * Get managmentResponsibility
     *
     * @return boolean
     */
    public function getManagmentResponsibility()
    {
        return $this->managmentResponsibility;
    }

    /**
     * Set qualificationChoice
     *
     * @param boolean $qualificationChoice
     *
     * @return Qualification
     */
    public function setQualificationChoice($qualificationChoice)
    {
        $this->qualificationChoice = $qualificationChoice;

        return $this;
    }

    /**
     * Get qualificationChoice
     *
     * @return boolean
     */
    public function getQualificationChoice()
    {
        return $this->qualificationChoice;
    }

    /**
     * Set educationChoice
     *
     * @param boolean $educationChoice
     *
     * @return Qualification
     */
    public function setEducationChoice($educationChoice)
    {
        $this->educationChoice = $educationChoice;

        return $this;
    }

    /**
     * Get educationChoice
     *
     * @return boolean
     */
    public function getEducationChoice()
    {
        return $this->educationChoice;
    }

    /**
     * Set qualificationSection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\QualificationSection $qualificationSection
     *
     * @return Qualification
     */
    public function setQualificationSection(\Theaterjobs\ProfileBundle\Entity\QualificationSection $qualificationSection = null)
    {
        $this->qualificationSection = $qualificationSection;

        return $this;
    }

    /**
     * Get qualificationSection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\QualificationSection
     */
    public function getQualificationSection()
    {
        return $this->qualificationSection;
    }

    /**
     * Set profession
     *
     * @param string
     *
     * @return Qualification
     */
    public function setProfession ($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession
     *
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set categories
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $categories
     *
     * @return Qualification
     */
    public function setCategories(\Theaterjobs\CategoryBundle\Entity\Category $categories = null)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return \Theaterjobs\CategoryBundle\Entity\Category
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set organizationRelated
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizationRelated
     *
     * @return Qualification
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
     * Helper function to validate finished attr
     *
     * @param string $value
     * @return bool
     */
    public function checkDate($value) {
        if ($value == false) {
            return true;
        }
        $now = Carbon::createFromFormat('Y',Carbon::now()->format('Y'));
        $years = Carbon::createFromFormat("Y", $this->getEndDate());

        return ($now->diffInYears($years, false) <= 0);
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