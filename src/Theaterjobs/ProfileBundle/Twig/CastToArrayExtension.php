<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\ProfileBundle\Twig;

/**
 * Description of Cast To Array
 *
 * @author Redjan Ymeraj <ymerajr@yahoo.com>
 */
class CastToArrayExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('cast_to_array', array($this, 'castToArray')),
        );
    }

    public function castToArray($stdClassObject)
    {
        $response = array();
        foreach ($stdClassObject as $key => $value) {
            $response[$key] = $value;
        }
        return $response;
    }

    public function getName()
    {
        return 'cast_to_array_extension';
    }
}
