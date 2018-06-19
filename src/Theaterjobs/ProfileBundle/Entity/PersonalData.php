<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PersonalData
 *
 * @ORM\Table(name="tj_profile_personal_data"))
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\PersonalDataRepository")
 */
class PersonalData
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
     * @var \DateTime
     *
     * @ORM\Column(name="birthDate", type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     * @return PersonalData
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="birthPlace", type="string", length=255, nullable=true)
     */
    private $birthPlace;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="integer", nullable=true)
     */
    private $nationality;

    /**
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     * @return PersonalData
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="ageRoleFrom", type="integer", nullable=true)
     */
    private $ageRoleFrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="ageRoleTo", type="integer", nullable=true)
     */
    private $ageRoleTo;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var integer
     *
     * @ORM\Column(name="shoeSize", type="integer", nullable=true)
     */
    private $shoeSize;

    /**
     * @var integer
     *
     * @ORM\Column(name="clothesSize", type="integer", nullable=true)
     */
    private $clothesSize;

    /**
     * @ORM\OneToOne(targetEntity="Profile", inversedBy="personalData")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    private $profile;

    /**
     * @ORM\ManyToOne(targetEntity="HairColor", inversedBy="personalData")
     * @ORM\JoinColumn(name="tj_profile_haircolorsid", referencedColumnName="id")
     */
    private $hairColor;

    /**
     * @ORM\ManyToOne(targetEntity="EyeColor", inversedBy="personalData")
     * @ORM\JoinColumn(name="tj_profile_eyecolorsid", referencedColumnName="id")
     */
    private $eyeColor;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Model\CategoryInterface")
     * @ORM\JoinTable(name="tj_profile_personaldata_voicecategories")
     */
    private $voiceCategories;

    /**
     * Add voiceCategories
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $voiceCategories
     * @return SingerSection
     */
    public function addVoiceCategory(\Theaterjobs\CategoryBundle\Entity\Category $voiceCategories)
    {
        $this->voiceCategories[] = $voiceCategories;

        return $this;
    }

    /**
     * Remove voiceCategories
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $voiceCategories
     */
    public function removeVoiceCategory(\Theaterjobs\CategoryBundle\Entity\Category $voiceCategories)
    {
        $this->voiceCategories->removeElement($voiceCategories);
    }

    /**
     * Get voiceCategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVoiceCategories()
    {
        return $this->voiceCategories;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->voiceCategories = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     * @return PersonalData
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHairColor()
    {
        return $this->hairColor;
    }

    /**
     * @param mixed $hairColor
     * @return PersonalData
     */
    public function setHairColor($hairColor)
    {
        $this->hairColor = $hairColor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEyeColor()
    {
        return $this->eyeColor;
    }

    /**
     * @param mixed $eyeColor
     * @return PersonalData
     */
    public function setEyeColor($eyeColor)
    {
        $this->eyeColor = $eyeColor;
        return $this;
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
     * Set birthPlace
     *
     * @param string $birthPlace
     *
     * @return PersonalData
     */
    public function setBirthPlace($birthPlace)
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * Get birthPlace
     *
     * @return string
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    /**
     * Set ageRoleFrom
     *
     * @param integer $ageRoleFrom
     *
     * @return PersonalData
     */
    public function setAgeRoleFrom($ageRoleFrom)
    {
        $this->ageRoleFrom = $ageRoleFrom;

        return $this;
    }

    /**
     * Get ageRoleFrom
     *
     * @return integer
     */
    public function getAgeRoleFrom()
    {
        return $this->ageRoleFrom;
    }

    /**
     * Set ageRoleTo
     *
     * @param integer $ageRoleTo
     *
     * @return PersonalData
     */
    public function setAgeRoleTo($ageRoleTo)
    {
        $this->ageRoleTo = $ageRoleTo;

        return $this;
    }

    /**
     * Get ageRoleTo
     *
     * @return integer
     */
    public function getAgeRoleTo()
    {
        return $this->ageRoleTo;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return PersonalData
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set shoeSize
     *
     * @param integer $shoeSize
     *
     * @return PersonalData
     */
    public function setShoeSize($shoeSize)
    {
        $this->shoeSize = $shoeSize;

        return $this;
    }

    /**
     * Get shoeSize
     *
     * @return integer
     */
    public function getShoeSize()
    {
        return $this->shoeSize;
    }

    /**
     * Set clothesSize
     *
     * @param integer $clothesSize
     *
     * @return PersonalData
     */
    public function setClothesSize($clothesSize)
    {
        $this->clothesSize = $clothesSize;

        return $this;
    }

    /**
     * Get clothesSize
     *
     * @return integer
     */
    public function getClothesSize()
    {
        return $this->clothesSize;
    }

    /**
     * @return bool
     */
    public function showContent()
    {
        return !empty($this->birthDate)
            || !empty($this->birthPlace)
            || !empty($this->nationality)
            || !empty($this->ageRoleFrom)
            || !empty($this->ageRoleTo)
            || !empty($this->height)
            || !empty($this->clothesSize)
            || !empty($this->shoeSize)
            || !empty($this->eyeColor)
            || !empty($this->hairColor)
            || $this->voiceCategories->count() > 0;

    }
}

