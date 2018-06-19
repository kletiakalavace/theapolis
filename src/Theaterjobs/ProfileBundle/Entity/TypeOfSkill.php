<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category for Skill entity
 * 
 * @ORM\Entity
 * @ORM\Table( name="tj_profile_typeOfSkill" ) 
 */
class TypeOfSkill
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column=(name="title" , type="string", length=255)
     */
    protected $title;
    
    /**
     * @ORM\OneToMany( targetEntity="Skill" , mappedBy="typeOfSkill" )
     */
    protected $skill;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="isRateable", type="boolean")
     */
    protected $isRateable;
    
     public function __construct()
    {
        $this->skill = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

 

    public function getIsRateable() {
        return $this->isRateable;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setIsRateable($isRateable) {
        $this->isRateable = $isRateable;
    }
    
    public function addSkill(Skill $skill)
    {
        $this->skill[] = $skill;
        return $this;
    }
    
    public function getSkill()
    {
        return $this->skill;
    }
    
    public function removeSkill(Skill $skill)
    {
        $this->skill->removeElement($skill);
    }
}
