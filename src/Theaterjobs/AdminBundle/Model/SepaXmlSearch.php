<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 11/03/2018
 * Time: 20:51
 */

namespace Theaterjobs\AdminBundle\Model;


class SepaXmlSearch
{
    protected $order;

    protected $orderCol;

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return SepaXmlSearch
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
     * @return SepaXmlSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}