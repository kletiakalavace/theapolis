<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 07/03/2018
 * Time: 22:22
 */

namespace Theaterjobs\AdminBundle\Model;


class AdminTeamMembershipSearch
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
     * @return AdminTeamMembershipSearch
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
     * @return AdminTeamMembershipSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}