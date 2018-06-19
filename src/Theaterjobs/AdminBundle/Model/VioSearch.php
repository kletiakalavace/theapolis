<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 10/03/2018
 * Time: 10:51
 */

namespace Theaterjobs\AdminBundle\Model;


class VioSearch
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
     * @return VioSearch
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
     * @return VioSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}