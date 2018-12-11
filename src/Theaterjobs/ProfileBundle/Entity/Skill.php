<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Holds the abstract class for skill.
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\SkillRepository")
 * @ORM\Table(name="tj_profile_skill")
 */
class Skill
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checkedAt", type="datetime", nullable=true)
     */
    private $checkedAt;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Skill")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @ORM\OneToMany(targetEntity="Skill", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToMany( targetEntity="LanguageSkill" , mappedBy="skill",cascade={"remove"} )
     */
    protected $languageSkill;

    /**
     * @ORM\ManyToOne( targetEntity="Profile", inversedBy="skillInserter" )
     * @ORM\JoinColumn(name="tj_profile_inserter_id", onDelete="CASCADE")
     */
    protected $inserter;


    /**
     * @ORM\ManyToMany(targetEntity="skillSection", mappedBy="skill",cascade={"persist","remove"}))
     */
    private $skillSection;

    /**
     * @var bool
     * @ORM\Column(name="checked", type="boolean", nullable=false)
     */
    protected $checked = false;

    /**
     * @var bool
     * @ORM\Column(name="is_language", type="boolean", nullable=false)
     */
    protected $isLanguage = false;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;


    public function __construct()
    {
        $this->skillSection = new ArrayCollection();
        $this->languageSkill = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $createdAt
     * @return Skill
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Skill
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }


    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Add SkillSection
     *
     * @param SkillSection $skillSection
     * @return Skill
     */
    public function addSkillSection(SkillSection $skillSection)
    {
        $this->skillSection[] = $skillSection;

        return $this;
    }

    /**
     * Remove SkillSection
     *
     * @param SkillSection $skillSection
     */
    public function removeSkillSection(SkillSection $skillSection)
    {
        $this->skillSection->removeElement($skillSection);
    }

    /**
     * Add languageSkill
     *
     * @param \Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill
     * @return Skill
     */
    public function addLanguageSkill(\Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill)
    {
//        $profileSkills->setProfile($this);
        $this->languageSkill[] = $languageSkill;

        return $this;
    }

    /**
     * Remove profileSkills
     *
     * @param \Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill
     */
    public function removeLanguageSkill(\Theaterjobs\ProfileBundle\Entity\LanguageSkill $languageSkill)
    {
        $this->languageSkill->removeElement($languageSkill);
    }

    /**
     * Get languageSkill
     *
     * @return Collection
     */
    public function getLanguageSkill()
    {
        return $this->languageSkill;
    }

    /**
     * Set parent
     *
     * @param Skill $parent
     * @return Skill
     */
    public function setParent(Skill $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Skill
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Skill $children
     * @return Skill
     */
    public function addChild(Skill $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Skill $children
     */
    public function removeChild(Skill $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set inserter
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $inserter
     * @return Skill
     */
    public function setInserter(\Theaterjobs\ProfileBundle\Entity\Profile $inserter = null)
    {
        $this->inserter = $inserter;

        return $this;
    }

    /**
     * Get inserter
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getInserter()
    {
        return $this->inserter;
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
     * @return boolean
     */
    public function getIsLanguage()
    {
        return $this->isLanguage;
    }

    /**
     * @param boolean $isLanguage
     */
    public function setIsLanguage($isLanguage)
    {
        $this->isLanguage = $isLanguage;
    }

    /**
     * @return \DateTime
     */
    public function getCheckedAt()
    {
        return $this->checkedAt;
    }

    /**
     * @param \DateTime $checkedAt
     * @return Skill
     */
    public function setCheckedAt($checkedAt)
    {
        $this->checkedAt = $checkedAt;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param mixed $lft
     * @return Skill
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param mixed $lvl
     * @return Skill
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param mixed $rgt
     * @return Skill
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSkillSection()
    {
        return $this->skillSection;
    }

}
