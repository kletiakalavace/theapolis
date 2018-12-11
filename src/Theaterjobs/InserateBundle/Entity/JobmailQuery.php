<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Table(name="tj_inserate_jobmailquery")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\JobmailQueryRepository")
 */
class JobmailQuery implements Translatable {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="searchword", type="string", length=255, nullable=true)
     */
    private $searchword;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface")
     * @ORM\JoinTable(name="tj_inserate_jobmail_categories",
     *      joinColumns={@ORM\JoinColumn(name="jobmail_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Gratification", inversedBy="jobmailQuery")
     * @ORM\JoinTable(name="tj_inserate_jobmail_gratification",
     *      joinColumns={@ORM\JoinColumn(name="jobmail_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="gratification_id", referencedColumnName="id")}
     *      )
     */
    private $gratification;

    /**
     * Gedmo\Translatable
     * @ORM\Column( name="fromAge", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 99,
     *      minMessage = "age.greater_equal.than.one",
     *      maxMessage = "age.lower_equal.than.ninety_nine"
     * )
     */
    protected $fromAge;

    /**
     * Gedmo\Translatable
     * @ORM\Column( name="toAge", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 99,
     *      minMessage = "age.greater_equal.than.one",
     *      maxMessage = "age.lower_equal.than.ninety_nine"
     * )
     */
    protected $toAge;

    /**
     * @ORM\Column( name="country", type="array", length=2 )
     */
    protected $country;

    /**
     * @ORM\Column( name="zip", type="array", length=10 )
     */
    protected $zip;

    /**
     * @ORM\Column( name="active", type="boolean")
     */
    protected $active = true;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(name="geolocation",type="string", length=255 ,nullable=true)
     */
    protected $geolocation;

    /**
     * @ORM\Column(name="radius",type="string", length=255 ,nullable=true)
     */
    protected $area;

    /**
     * @ORM\OneToMany(targetEntity="\Theaterjobs\InserateBundle\Entity\Jobmail", mappedBy="jobmailQuery", cascade={"remove"})
     */
    private $jobmail;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->gratification = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add gratification.
     *
     * @param \Theaterjobs\InserateBundle\Entity\Gratification $gratification
     *
     * @return Jobmail
     */
    public function addGratification(\Theaterjobs\InserateBundle\Entity\Gratification $gratification) {
        $this->gratification[] = $gratification;

        return $this;
    }

    /**
     * Remove gratification.
     *
     * @param \Theaterjobs\InserateBundle\Entity\Gratification $gratification
     */
    public function removeGratification(\Theaterjobs\InserateBundle\Entity\Gratification $gratification) {
        $this->gratification->removeElement($gratification);
    }

    /**
     * Get gratification.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGratification()
    {
        return $this->gratification;
    }

    /**
     * Set user.
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     *
     * @return Jobmail
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Add category.
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $category
     *
     * @return Jobmail
     */
    public function addCategory(\Theaterjobs\CategoryBundle\Entity\Category $category) {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category.
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $category
     */
    public function removeCategory(\Theaterjobs\CategoryBundle\Entity\Category $category) {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return JobmailQuery
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set fromAge.
     *
     * @param int $fromAge
     *
     * @return JobmailQuery
     */
    public function setFromAge($fromAge) {
        $this->fromAge = $fromAge;

        return $this;
    }

    /**
     * Get fromAge.
     *
     * @return int
     */
    public function getFromAge() {
        return $this->fromAge;
    }

    /**
     * Set toAge.
     *
     * @param int $toAge
     *
     * @return JobmailQuery
     */
    public function setToAge($toAge) {
        $this->toAge = $toAge;

        return $this;
    }

    /**
     * Get toAge.
     *
     * @return int
     */
    public function getToAge() {
        return $this->toAge;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return JobmailQuery
     */
    public function setActive($active) {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return JobmailQuery
     */
    public function setCountry($country) {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set zip.
     *
     * @param string $zip
     *
     * @return JobmailQuery
     */
    public function setZip($zip) {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip.
     *
     * @return string
     */
    public function getZip() {
        return $this->zip;
    }

    function getSearchword() {
        return $this->searchword;
    }

    function setSearchword($searchword) {
        $this->searchword = $searchword;
    }

    /**
     * Set geolocation
     *
     * @param string $geolocation
     *
     * @return JobmailQuery
     */
    public function setGeolocation($geolocation) {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * Get geolocation
     *
     * @return string
     */
    public function getGeolocation() {
        return $this->geolocation;
    }

    /**
     * Set area
     *
     * @param string $area
     *
     * @return JobmailQuery
     */
    public function setArea($area) {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string
     */
    public function getArea() {
        return $this->area;
    }

    /**
     * Add jobmail
     *
     * @param \Theaterjobs\InserateBundle\Entity\Jobmail $jobmail
     *
     * @return JobmailQuery
     */
    public function addJobmail(\Theaterjobs\InserateBundle\Entity\Jobmail $jobmail) {
        $this->jobmail[] = $jobmail;

        return $this;
    }

    /**
     * Remove jobmail
     *
     * @param \Theaterjobs\InserateBundle\Entity\Jobmail $jobmail
     */
    public function removeJobmail(\Theaterjobs\InserateBundle\Entity\Jobmail $jobmail) {
        $this->jobmail->removeElement($jobmail);
    }

    /**
     * Get jobmail
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobmail() {
        return $this->jobmail;
    }

}
