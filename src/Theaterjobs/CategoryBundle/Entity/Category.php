<?php

namespace Theaterjobs\CategoryBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\ProfileBundle\Model\CategoryInterface as ProfileCategory;
use Theaterjobs\InserateBundle\Model\CategoryInterface as InserateCategory;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="tj_category_categories")
 * @ORM\Entity(repositoryClass="Theaterjobs\CategoryBundle\Entity\CategoryRepository")
 *
 * @category Entity
 * @package  Theaterjobs\CategoryBundle\Entity
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class Category implements ProfileCategory, InserateCategory {

    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="oldCategories", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="tj_profile_old_categories",
     *      joinColumns={@ORM\JoinColumn(name="tj_category_categories_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tj_profile_profiles_id", referencedColumnName="id")}
     *      )
     */
    private $profiles;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=64)
     */
    private $title;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @Gedmo\Slug(
     *     fields={"title"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", type="string", length=128)
     */
    private $slug;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @ORM\Column(name="removedAt", type="datetime", nullable=true)
     */
    private $removedAt;

    /**
     * @ORM\Column(name="requiresAge", type="boolean")
     */
    protected $requiresAge = false;

    /**
     * @ORM\Column(name="is_performance_category", type="boolean")
     */
    protected $isPerformanceCategory = false;

    /**
     * @ORM\Column(name="entity_number", type="integer")
     */
    protected $entityNumber = 0;

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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    public function setTranslatableLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profiles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Category
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set parent
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $parent
     * @return Category
     */
    public function setParent(\Theaterjobs\CategoryBundle\Entity\Category $parent = null) {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Theaterjobs\CategoryBundle\Entity\Category
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $children
     * @return Category
     */
    public function addChild(\Theaterjobs\CategoryBundle\Entity\Category $children) {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $children
     */
    public function removeChild(\Theaterjobs\CategoryBundle\Entity\Category $children) {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren() {
        return $this->children;
    }

    function getRemovedAt() {
        return $this->removedAt;
    }

    function setRemovedAt($removedAt) {
        $this->removedAt = $removedAt;
    }

    function setRequiresAge($requiresAge) {
        $this->requiresAge = $requiresAge;
    }

    function getRequiresAge() {
        return $this->requiresAge;
    }

    /**
     * Set entityNumber
     *
     * @param integer $entityNumber
     *
     * @return Category
     */
    public function setEntityNumber($entityNumber) {
        $this->entityNumber = $entityNumber;

        return $this;
    }

    /**
     * Get entityNumber
     *
     * @return integer
     */
    public function getEntityNumber() {
        return $this->entityNumber;
    }

    /**
     * Set root
     *
     * @param integer $root
     *
     * @return Category
     */
    public function setRoot($root) {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot() {
        return $this->root;
    }

    /**
     * Set isPerformanceCategory
     *
     * @param boolean $isPerformanceCategory
     *
     * @return Category
     */
    public function setIsPerformanceCategory($isPerformanceCategory) {
        $this->isPerformanceCategory = $isPerformanceCategory;

        return $this;
    }

    /**
     * Get isPerformanceCategory
     *
     * @return boolean
     */
    public function getIsPerformanceCategory() {
        return $this->isPerformanceCategory;
    }

    function getDescription() {
        return $this->description;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return Category
     */
    public function setLft($lft) {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft() {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     *
     * @return Category
     */
    public function setLvl($lvl) {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl() {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     *
     * @return Category
     */
    public function setRgt($rgt) {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt() {
        return $this->rgt;
    }
    
     /**
     * Add profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return Category
     */
    public function addProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile)
    {
        $this->profiles[] = $profile;

        return $this;
    }

    /**
     * Remove profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     */
    public function removeProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile)
    {
        if (!$this->profiles->contains($profile)) {
            return;
        }
        $this->profiles->removeElement($profile);
        $profile->removeOldCategory($this);
    }

    /**
     * Get profiles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

}
