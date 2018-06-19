<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of SkillSection
 *
 * @ORM\Table(name="tj_profile_section_skill")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class SkillSection
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne( targetEntity="Profile", mappedBy="skillSection" )
     */
    protected $profile;

    /**
     * @ORM\ManyToMany(targetEntity="Skill", inversedBy="skillSection",cascade={"persist","remove"})
     * @ORM\JoinTable(name="tj_section_skill_relation",
     * joinColumns={@ORM\JoinColumn(name="section_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE")}
     *  )
     *
     */
    protected $skill;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Skill", cascade={"persist"})
     * @ORM\JoinTable(name="tj_profile_skill_relation",
     *      joinColumns={@ORM\JoinColumn(name="profileSkill_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * */
    protected $profileSkill;

    /**
     * @ORM\OneToMany( targetEntity="LanguageSkill" , mappedBy="skillSection" , cascade={"persist","remove"} )
     */
    protected $languageSkill;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Model\CategoryInterface")
     * @ORM\JoinTable(name="tj_profile_profiles_drivelicence")
     */
    protected $driveLicense;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->skill = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profileSkill = new \Doctrine\Common\Collections\ArrayCollection();
        $this->languageSkill = new \Doctrine\Common\Collections\ArrayCollection();
        $this->driveLicense = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     * @return SkillSection
     */
    public function setProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile = null)
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
     * Add profileSkill
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Skill $profileSkill
     * @return SkillSection
     */
    public function addProfileSkill(\Theaterjobs\ProfileBundle\Entity\Skill $profileSkill)
    {
        if (!$this->skill->contains($profileSkill)) {
            $this->skill->add($profileSkill);
        }

        if (!$this->profileSkill->contains($profileSkill)) {
            $this->profileSkill->add($profileSkill);
        }

        return $this;
    }

    /**
     * Remove profileSkill
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Skill $profileSkill
     */
    public function removeProfileSkill(\Theaterjobs\ProfileBundle\Entity\Skill $profileSkill)
    {
        $this->skill->removeElement($profileSkill);
        $this->profileSkill->removeElement($profileSkill);
    }

    /**
     * Get profileSkill
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfileSkill()
    {
        return $this->profileSkill;
    }

    /**
     * Add languageSkill
     *
     * @param \Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill
     * @return SkillSection
     */
    public function addLanguageSkill(\Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill)
    {
        $languageSkill->setSkillSection($this);
        $this->languageSkill[] = $languageSkill;

        return $this;
    }

    /**
     * Remove languageSkill
     *
     * @param \Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill
     */
    public function removeLanguageSkill(\Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill)
    {
        $languageSkill->setSkillSection(null);
        $this->languageSkill->removeElement($languageSkill);
    }

    /**
     * Get profileSkill
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLanguageSkill()
    {
        return $this->languageSkill;
    }

    /**
     * Add driveLicense
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $driveLicense
     * @return SkillSection
     */
    public function addDriveLicense(\Theaterjobs\CategoryBundle\Entity\Category $driveLicense)
    {
        $this->driveLicense[] = $driveLicense;

        return $this;
    }

    /**
     * Remove driveLicense
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $driveLicense
     */
    public function removeDriveLicense(\Theaterjobs\CategoryBundle\Entity\Category $driveLicense)
    {
        $this->driveLicense->removeElement($driveLicense);
    }

    /**
     * Get driveLicense
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDriveLicense()
    {
        return $this->driveLicense;
    }

    /**
     * @return bool
     */
    public function showContent()
    {
        return $this->driveLicense->count() > 0
            || $this->languageSkill->count() > 0
            || $this->profileSkill->count() > 0;
    }
}
