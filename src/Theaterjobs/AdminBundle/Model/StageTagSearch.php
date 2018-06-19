<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 09/03/2018
 * Time: 22:24
 */

namespace Theaterjobs\AdminBundle\Model;


class StageTagSearch
{
    protected $title;

    protected $order = 'desc';

    protected $orderCol = 'updatedAt';

    protected $choices;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return StageTagSearch
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return StageTagSearch
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
     * @return StageTagSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param mixed $choices
     * @return StageTagSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

}