<?php

namespace Theaterjobs\NewsBundle\Model;


/**
 * The NewsSearch Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination criterias.
 *
 * @category Model
 * @package  Theaterjobs\NewsBundle\Model
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
/**
 * Class NewsSearch
 * @package Theaterjobs\NewsBundle\Model
 */
class NewsSearch
{
    /**
     * @var
     */
    protected $searchPhrase;

    // published or not
    /**
     * @var bool
     */
    protected $published = true;

    // the default main category
    /**
     * @var
     */
    protected $categories;

    /**
     * @var
     */
    protected $years;

    // properties that are not mapped are only used to save data from the search
    /**
     * @var
     */
    protected $tags;

    /**
     * @var
     */
    protected $organization;

    /**
     * @var bool
     */
    protected $favorite = false;

    /**
     * @var array
     */
    protected $newsFavourites = [];

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
     * @return NewsSearch
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
     * @return NewsSearch
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewsFavourites()
    {
        return $this->newsFavourites;
    }

    /**
     * @param array $newsFavourites
     * @return NewsSearch
     */
    public function setNewsFavourites($newsFavourites)
    {
        $this->newsFavourites = $newsFavourites;
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
     * @return NewsSearch
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
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
     * @return NewsSearch
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
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
     * @return NewsSearch
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
     * @return NewsSearch
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     * @return NewsSearch
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * @param mixed $years
     * @return NewsSearch
     */
    public function setYears($years)
    {
        $this->years = $years;
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
