<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 26/02/2018
 * Time: 20:51
 */

namespace Theaterjobs\AdminBundle\Model;


class JobListSearch
{
    protected $order = 'desc';

    protected $orderCol = 'createdAt';

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return JobListSearch
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
     * @return JobListSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}