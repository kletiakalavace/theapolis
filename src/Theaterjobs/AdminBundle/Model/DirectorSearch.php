<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 25/02/2018
 * Time: 11:05
 */

namespace Theaterjobs\AdminBundle\Model;


class DirectorSearch
{
    protected $name;

    protected $order = 'desc';

    protected $orderCol = 'updatedAt';

    protected $published = 0;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return DirectorSearch
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return DirectorSearch
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
     * @return DirectorSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param mixed $published
     * @return DirectorSearch
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

}