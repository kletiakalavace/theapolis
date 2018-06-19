<?php

namespace Theaterjobs\MainBundle\Utility\Traits;

/**
 * Trait ESIndexTypeConfTrait
 * @package Theaterjobs\MainBundle\Utility\Traits
 */
trait ESIndexTypeConfTrait
{
    /**
     * @var $className
     * @return string
     * @throws \Exception
     */
    public function getIndexName($className)
    {
        $indexes = array_keys($this->get('fos_elastica.index_manager')->getAllIndexes());
        foreach ($indexes as $index) {
            $conf = $this->get('fos_elastica.config_manager')->getIndexConfiguration($index);
            $type = array_filter($conf->getTypes(), function ($item) use ($className) {
                return $className === $item->getModel();
            });
            if ($type) {
                return "$index." . key($type);
            }
        }
        throw new \Exception("There is no type defined for class name $className");
    }

}