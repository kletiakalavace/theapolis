<?php

namespace Theaterjobs\InserateBundle\Twig\Extension;

/**
 * Class UnSerialize
 * @author Marlind Parllaku <marlind93@gmail.com>
 * @package Theaterjobs\InserateBundle\Twig\Extension
 */
class UnSerialize extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('unserialize', array($this, 'unSerialize')),
        );
    }

    public function unSerialize($serializedArray)
    {
        return unserialize($serializedArray);
    }
}
