<?php

namespace Theaterjobs\InserateBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Theaterjobs\UserBundle\Entity\User;

/**
 * The JobSerch Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination criterias.
 *
 * @category Model
 * @package  Theaterjobs\InserateBundle\Model
 * @author   Igli Hoxha <igliihoxha@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
class JobSearch
{
    /**
     * @var ArrayCollection
     */
    protected $gratification;

    /**
     * @var
     */
    protected $location;

    /**
     * @var ArrayCollection
     */
    protected $area;

    /**
     * @var
     */
    protected $searchPhrase;

    /**
     * @var array
     */
    protected $status = [1];

    /**
     * @var array
     */
    protected $createMode = [];

    // check to show or not user job favorite
    /**
     * @var bool
     */
    protected $favorite = false;

    // check to show or not applied jobs
    /**
     * @var bool
     */
    protected $applied = false;

    /**
     * @var array
     */
    protected $jobFavourites = [];

    // the default main category
    /**
     * @var
     */
    protected $category;

    /**
     * @var array
     */
    protected $subcategories=[];

    /**
     * @var
     */
    protected $sortBy;

    /**
     * @var
     */
    protected $organization;

    /**
     * @var
     */
    protected $user;

    /**
     * @var
     */
    protected $jobApplications;

    //If this is set to true the search function will return only today jobs.
    /**
     * @var bool
     */
    protected $savedSearch = false;

    /**
     * @var int
     */
    protected $page = 1;


    /**
     * JobSearch constructor.
     */
    public function __construct()
    {
        $this->area = new ArrayCollection();
        $this->gratification = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isApplied()
    {
        return $this->applied;
    }

    /**
     * @param bool $applied
     * @return JobSearch
     */
    public function setApplied($applied)
    {
        $this->applied = $applied;
        return $this;
    }


    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return JobSearch
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }


    /**
     * @return bool
     */
    public function isFavorite()
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     * @return JobSearch
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     * @return JobSearch
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param ArrayCollection $area
     * @return JobSearch
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearchPhrase()
    {
        return $this->searchPhrase;
    }

    /**
     * @param mixed $searchPhrase
     * @return JobSearch
     */
    public function setSearchPhrase($searchPhrase)
    {
        $this->searchPhrase = $searchPhrase;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     * @return JobSearch
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }


    /**
     * @return array
     */
    public function getSubcategories()
    {
        return $this->subcategories;
    }

    /**
     * @param array $subcategories
     * @return JobSearch
     */
    public function setSubcategories($subcategories)
    {
        $this->subcategories = $subcategories;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobFavourites()
    {
        return $this->jobFavourites;
    }

    /**
     * @param mixed $jobFavourites
     * @return JobSearch
     */
    public function setJobFavourites($jobFavourites)
    {
        $this->jobFavourites = $jobFavourites;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     * @return JobSearch
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGratification()
    {
        return $this->gratification;
    }

    /**
     * @param ArrayCollection $gratification
     * @return JobSearch
     */
    public function setGratification($gratification)
    {
        $this->gratification = $gratification;
        return $this;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param array $status
     * @return JobSearch
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getCreateMode()
    {
        return $this->createMode;
    }

    /**
     * @param mixed $createMode
     */
    public function setCreateMode($createMode)
    {
        $this->createMode = $createMode;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return JobSearch
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobApplications()
    {
        return $this->jobApplications;
    }

    /**
     * @param mixed $jobApplications
     */
    public function setJobApplications($jobApplications)
    {
        $this->jobApplications = $jobApplications;
    }

    /**
     * @return bool
     */
    public function isSavedSearch()
    {
        return $this->savedSearch;
    }

    /**
     * @param bool $savedSearch
     * @return JobSearch
     */
    public function setSavedSearch($savedSearch)
    {
        $this->savedSearch = $savedSearch;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassVars()
    {
        return get_class_vars(__CLASS__);
    }

    /**
     * Set inside instance params dynamically if they exists
     * @param $var
     * @param $value
     * @return bool
     */
    public function setVar($var, $value)
    {
        try {
            $this->$var = is_array($this->$var) && is_string($value) ? [$value] : $value;
        } catch (\Exception $e) {
            return false;
        }
    }
}
