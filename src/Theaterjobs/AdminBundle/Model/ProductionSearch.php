<?php

namespace Theaterjobs\AdminBundle\Model;


/**
 * The AdminProductionSearch Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination criteria.
 *
 * @category Model
 * @package  Theaterjobs\AdminBundle\Model
 * @author   Marlind Parllaku <marlind93@@gmail.com>
 * @link     http://www.theapolis.de
 */
class ProductionSearch
{
    protected $input;

    protected $name;

    protected $director;

    protected $creator;

    protected $organization;

    protected $year;

    protected $status;

    protected $choices;

    protected $order;

    protected $orderCol;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return ProductionSearch
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderCol()
    {
        return $this->orderCol;
    }

    /**
     * @param mixed $orderCol
     * @return ProductionSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     * @return ProductionSearch
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return ProductionSearch
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * @param string $director
     * @return ProductionSearch
     */
    public function setDirector($director)
    {
        $this->director = $director;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param string $creator
     * @return ProductionSearch
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
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
     * @param string $organization
     * @return ProductionSearch
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return ProductionSearch
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param mixed $choices
     * @return ProductionSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return ProductionSearch
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
