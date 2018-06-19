<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LanguageSkill
 *
 * @ORM\Table(name="tj_profile_profiles_languages")
 * @ORM\Entity
 */
class LanguageSkill
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
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\SkillSection", inversedBy="languageSkill", cascade={"persist"})
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
     **/
    private $skillSection;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Skill", inversedBy="languageSkill")
     * @ORM\JoinColumn(name="skill", referencedColumnName="id")
     **/
    private $skill;

    /**
     * @var integer
     * @ORM\Column(name="skill_rating", type="integer",nullable=true)
     */
    protected $rating;

    /**
     * @var int
     * @ORM\Column(name="tj_profile_skill_type",type="integer",nullable=true)
     */
    private $skillType=0;


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
     * Set skillSection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\SkillSection $skillSection
     * @return LanguageSkill
     */
    public function setSkillSection(\Theaterjobs\ProfileBundle\Entity\SkillSection $skillSection = null)
    {
        $this->skillSection = $skillSection;

        return $this;
    }

    /**
     * Get skillSection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\SkillSection
     */
    public function getSkillSection()
    {
        return $this->skillSection;
    }


    /**
     * Set skill
     *
     * @param integer $skill
     *
     * @return LanguageSkill
     */
    public function setSkill($skill)
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill
     *
     * @return integer
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * Set skillType
     *
     * @param integer $skillType
     *
     * @return LanguageSkill
     */
    public function setSkillType($skillType)
    {
        $this->skillType = $skillType;

        return $this;
    }

    /**
     * Get skillType
     *
     * @return integer
     */
    public function getSkillType()
    {
        return $this->skillType;
    }


    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
    }


}

