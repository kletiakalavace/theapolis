<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 06/02/2018
 * Time: 23:12
 */


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\ProfileBundle\Twig;

/**
 * Description of CountryExtension
 *
 * @author Malvin
 */
class CountryExtension extends \Twig_Extension {
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('country', array($this, 'countryFilter')),
        );
    }

    public function countryFilter($countryCode,$locale = "en"){
        $c = \Symfony\Component\Locale\Locale::getDisplayCountries($locale);

        return array_key_exists($countryCode, $c)
            ? $c[$countryCode]
            : $countryCode;
    }

    public function getName()
    {
        return 'country_extension';
    }
}