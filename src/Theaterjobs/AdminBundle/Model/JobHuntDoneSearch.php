<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 25/02/2018
 * Time: 20:32
 */

namespace Theaterjobs\AdminBundle\Model;


class JobHuntDoneSearch
{
    protected $name;

    protected $order = 'desc';

    protected $orderCol = 'createdAt';

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return JobHuntDoneSearch
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return JobHuntDoneSearch
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderCol()
    {
        return $this->orderCol;
    }

    /**
     * @param string $orderCol
     * @return JobHuntDoneSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }
}