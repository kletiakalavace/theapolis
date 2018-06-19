<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 22/03/2018
 * Time: 19:41
 */

namespace Theaterjobs\AdminBundle\Model;


class SocialMediaSearch
{
    protected $order = 'desc';

    protected $orderCol = 'updatedAt';

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return SocialMediaSearch
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
     * @return SocialMediaSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}