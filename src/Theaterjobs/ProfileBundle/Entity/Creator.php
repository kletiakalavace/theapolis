<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Creator
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="tj_profile_production_creator")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\CreatorRepository")
 */
class Creator
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
     * @ORM\ManyToMany(targetEntity="Production", mappedBy="creators")
     */
    protected $productions;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checkedAt", type="datetime", nullable=true)
     */
    private $checkedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var bool
     * @ORM\Column(name="checked", type="boolean", nullable=false)
     */
    protected $checked = false;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Creator", inversedBy="children")
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
     * @ORM\ManyToOne(targetEntity="Creator")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @ORM\OneToMany(targetEntity="Creator", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->productions = new ArrayCollection();
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
     * @return Creator
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
     * Add production
     *
     * @param Production $productions
     *
     * @return Creator
     */
    public function addProductions(Production $productions)
    {
        if (!$this->productions->contains($productions)) {
            $this->productions->add($productions);
        }

        return $this;
    }

    /**
     * Remove productions
     *
     * @param Production $productions
     */
    public function removeProductions(Production $productions)
    {
        $this->productions->removeElement($productions);
    }

    /**
     * @return ArrayCollection
     */
    public function getProductions()
    {
        return $this->productions;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     * @return Creator
     */
    public function setParent(Creator $parent = null)
    {
        $this->parent = $parent;
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
     * @return Creator
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
     * @return Creator
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
     * @return Creator
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add children
     *
     * @param Creator $children
     * @return Creator
     */
    public function addChild(Creator $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Creator $children
     */
    public function removeChild(Creator $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Creator
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
     * @return Creator
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCheckedAt()
    {
        return $this->checkedAt;
    }

    /**
     * @param mixed $checkedAt
     * @return Creator
     */
    public function setCheckedAt($checkedAt)
    {
        $this->checkedAt = $checkedAt;
        return $this;
    }

}

