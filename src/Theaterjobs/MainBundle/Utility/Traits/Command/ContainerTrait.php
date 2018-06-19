<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait ContainerTrait
 */
trait ContainerTrait
{
    /**
     * @param $string
     * @return object
     */
    public function get($string)
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();
        return $container->get($string);
    }
}