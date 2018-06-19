<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeOfCategory
 *
 * @ORM\Table(name="tj_profile_typeOfCategories")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\TypeOfCategoryRepository")
 */
class TypeOfCategory {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Model\CategoryInterface")
     * @ORM\JoinTable(name="tj_profile_typeOfCategories_categories")
     */
    protected $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * Constructor
     */
    public function __construct() {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return TypeOfCategory
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
     * Add categories
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $categories
     * @return TypeOfCategory
     */
    public function addCategory(\Theaterjobs\CategoryBundle\Entity\Category $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $categories
     */
    public function removeCategory(\Theaterjobs\CategoryBundle\Entity\Category $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
