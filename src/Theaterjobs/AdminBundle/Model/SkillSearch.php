<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 11/03/2018
 * Time: 12:31
 */

namespace Theaterjobs\AdminBundle\Model;


class SkillSearch
{
    protected $title;

    protected $order = 'desc';

    protected $orderCol = 'updatedAt';

    protected $choices;

    protected $isLanguage;

    /**
     * @return bool
     */
    public function isLanguage()
    {
        return $this->isLanguage;
    }

    /**
     * @param bool $isLanguage
     * @return SkillSearch
     */
    public function setIsLanguage($isLanguage)
    {
        $this->isLanguage = $isLanguage;
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
     * @return SkillSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return SkillSearch
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
     * @return SkillSearch
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
     * @return SkillSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

}