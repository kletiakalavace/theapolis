<?php

namespace Theaterjobs\MainBundle\Twig;

//use Symfony\Component\Locale\Locale;
use Symfony\Component\Intl\Intl;

/**
 * Country Filter
 *
 * @author Mirela Ndreu <mirelandreu89@gmail.com>
 */
class CountryExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('country', [$this, 'countryFilter'])
        );
    }

    public function countryFilter($countryCode, $locale = "en")
    {
        //TODO check implementation
//        $c = Locale::getDisplayCountries($locale);
//
//        return array_key_exists($countryCode, $c)
//            ? $c[$countryCode]
//            : $countryCode;
        return Intl::getRegionBundle()->getCountryName($countryCode, $locale);
    }

    public function getName()
    {
        return 'country_extension';
    }
}