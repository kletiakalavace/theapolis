<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 02/03/2018
 * Time: 17:54
 */

namespace Theaterjobs\AdminBundle\Model;


class NameChangeRequestSearch
{

    protected $order = 'desc';

    protected $orderCol = 'publishedAt';

    protected $choices;

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param mixed $choices
     * @return NameChangeRequestSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
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
     * @return NameChangeRequestSearch
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
     * @return NameChangeRequestSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}