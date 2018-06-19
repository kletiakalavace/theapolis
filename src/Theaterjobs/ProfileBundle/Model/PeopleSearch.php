<?php

namespace Theaterjobs\ProfileBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * The PeopleSerch Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination criterias.
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
class PeopleSearch
{
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

    // published or not
    /**
     * @var int
     */
    protected $published = 1;

    /**
     * @var array
     */
    protected $userFavourites = [];

    // check display user favorite results
    /**
     * @var bool
     */
    protected $favorite = false;

    /**
     * @var
     */
    protected $category;

    /**
     * @var array
     */
    protected $subcategories = [];

    /**
     * @var
     */
    protected $sortChoices;

    /**
     * @var
     */
    protected $organization;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * PeopleSearch constructor.
     */
    public function __construct()
    {
        $this->area = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     * @return PeopleSearch
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }


    /**
     * @return int
     */
    public function isFavorite()
    {
        return $this->favorite;
    }

    /**
     * @param int $favorite
     * @return PeopleSearch
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
     * @return PeopleSearch
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
     * @return PeopleSearch
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
     * @return PeopleSearch
     */
    public function setSearchPhrase($searchPhrase)
    {
        $this->searchPhrase = $searchPhrase;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     * @return PeopleSearch
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortChoices()
    {
        return $this->sortChoices;
    }

    /**
     * @param string $sortChoices
     * @return PeopleSearch
     */
    public function setSortChoices($sortChoices)
    {
        $this->sortChoices = $sortChoices;
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
     * @return PeopleSearch
     */
    public function setSubcategories($subcategories)
    {
        $this->subcategories = $subcategories;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserFavourites()
    {
        return $this->userFavourites;
    }

    /**
     * @param mixed $userFavourites
     * @return PeopleSearch
     */
    public function setUserFavourites($userFavourites)
    {
        $this->userFavourites = $userFavourites;
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
     * @return PeopleSearch
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
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
     * @return PeopleSearch
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassVars()
    {
        return get_class_vars(__CLASS__);
    }
}
