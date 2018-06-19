<?php

namespace Theaterjobs\MainBundle\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;
use Theaterjobs\MainBundle\Entity\SaveSearch;

/**
 * Class SaveSearchEntityTransformer
 * @package Theaterjobs\MainBundle\Form\DataTransformer
 */
class SaveSearchEntityTransformer implements DataTransformerInterface
{

    /**
     * @inheritdoc
     */
    public function transform($entityName)
    {
        // Returns index where $entity name is
        return array_search($entityName, SaveSearch::VALID_ENTITIES);
    }
    /**
     * @inheritdoc
     */
    public function reverseTransform($entityShortcut)
    {
        return SaveSearch::VALID_ENTITIES[$entityShortcut];
    }
}