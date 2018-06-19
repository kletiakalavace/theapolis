<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 25/02/2018
 * Time: 18:51
 */

namespace Theaterjobs\AdminBundle\Model;


class JobHuntSearch
{

    protected $name;

    protected $order = 'desc';

    protected $orderCol = 'updatedAt';

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return JobHuntSearch
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
     * @return JobHuntSearch
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
     * @return JobHuntSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}