<?php

namespace Theaterjobs\MainBundle\Twig;

/**
 * The twig exstension
 *
 * @category Twig
 * @package  Theaterjobs\MainBundle\Twig
 */
class JsonDecodeExtention extends \Twig_Extension {

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecodeFilter')),
        );
    }

    public function jsonDecodeFilter($jsonObject, $option)
    {
        return json_decode($jsonObject, $option);
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     *
     * @return string
     */
    public function getName() {
        return 'json_decode';
    }

}
