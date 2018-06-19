<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 01/03/2018
 * Time: 15:09
 */

namespace Theaterjobs\AdminBundle\Model;


class JobRequestSearch
{
    protected $status;

    protected $order = 'desc';

    protected $orderCol = 'requestedPublicationAt';

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return JobRequestSearch
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return JobRequestSearch
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
     * @return JobRequestSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}