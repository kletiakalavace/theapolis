<?php

namespace Theaterjobs\InserateBundle\Model;

use Theaterjobs\UserBundle\Entity\User;


/**
 * The OrganizationSearch Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination criteria.
 *
 * @category Model
 * @package  Theaterjobs\InserateBundle\Model
 * @author   Igli Hoxha <igliihoxha@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
class OrganizationSearch
{
    /**
     * @var
     */
    protected $location;

    /**
     * @var
     */
    protected $area;

    /**
     * @var
     */
    protected $searchPhrase;

    /**
     * @var
     */
    protected $sortChoices;

    /**
     * @var bool
     */
    protected $organizationFavourites = false;

    /**
     * @var bool
     */
    protected $myOrganizations = false;

    /**
     * @var
     */
    protected $organizationKind;

    /**
     * @var
     */
    protected $organizationSection;

    /**
     * @var array
     */
    protected $status = [2];

    /**
     * @var
     */
    protected $isVisibleInList;

    /**
     * @var bool
     */
    protected $defaultStatus = false;

    /**
     * @var bool
     */
    protected $favorite = false;

    /**
     * @var
     */
    protected $organization;

    // properties that are not mapped are only used to save data from the search
    /**
     * @var
     */
    protected $tags = [];

    /**
     * @var
     */
    protected $forUser;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return OrganizationSearch
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     * @return OrganizationSearch
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortChoices()
    {
        return $this->sortChoices;
    }

    /**
     * @param mixed $sortChoices
     * @return OrganizationSearch
     */
    public function setSortChoices($sortChoices)
    {
        $this->sortChoices = $sortChoices;
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
     * @return OrganizationSearch
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     * @return OrganizationSearch
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
     * @return OrganizationSearch
     */
    public function setSearchPhrase($searchPhrase)
    {
        $this->searchPhrase = $searchPhrase;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganizationFavourites()
    {
        return $this->organizationFavourites;
    }

    /**
     * @param mixed $organizationFavourites
     * @return OrganizationSearch
     */
    public function setOrganizationFavourites($organizationFavourites)
    {
        $this->organizationFavourites = $organizationFavourites;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganizationKind()
    {
        return $this->organizationKind;
    }

    /**
     * @param mixed $organizationKind
     * @return OrganizationSearch
     */
    public function setOrganizationKind($organizationKind)
    {
        $this->organizationKind = $organizationKind;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganizationSection()
    {
        return $this->organizationSection;
    }

    /**
     * @param mixed $organizationSection
     * @return OrganizationSearch
     */
    public function setOrganizationSection($organizationSection)
    {
        $this->organizationSection = $organizationSection;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getForUser()
    {
        return $this->forUser;
    }

    /**
     * @param $forUser User
     * @return OrganizationSearch
     */
    public function setForUser($forUser)
    {
        $this->forUser = $forUser;
        return $this;
    }


    /**
     * @return bool
     */
    public function isDefaultStatus()
    {
        return $this->defaultStatus;
    }

    /**
     * @param bool $defaultStatus
     * @return OrganizationSearch
     */
    public function setDefaultStatus($defaultStatus)
    {
        $this->defaultStatus = $defaultStatus;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVisibleInList()
    {
        return $this->isVisibleInList;
    }

    /**
     * @param bool $isVisibleInList
     * @return OrganizationSearch
     */
    public function setIsVisibleInList($isVisibleInList)
    {
        $this->isVisibleInList = $isVisibleInList;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMyOrganizations()
    {
        return $this->myOrganizations;
    }

    /**
     * @param mixed $myOrganizations
     * @return OrganizationSearch
     */
    public function setMyOrganizations($myOrganizations)
    {
        $this->myOrganizations = $myOrganizations;
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
     * @return OrganizationSearch
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;
        return $this;
    }


    /**
     * @return bool
     */
    public function isOrganization()
    {
        return $this->organization;
    }

    /**
     * @param bool $organization
     * @return OrganizationSearch
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
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
     * @return OrganizationSearch
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param array $status
     * @return OrganizationSearch
     */
    public function addStatus($status)
    {
        $this->status[] = $status;
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
