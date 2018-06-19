<?php
namespace Theaterjobs\MainBundle\Transformer;

use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

class ElasticaToRawTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * {@inheritDoc}
     **/
    function transform(array $elasticaObjects)
    {
        return $elasticaObjects;
    }

    /**
     * {@inheritDoc}
     **/
    function hybridTransform(array $elasticaObjects)
    {
        return $elasticaObjects;
    }

    /**
     * {@inheritDoc}
     **/
    function getObjectClass()
    {
    }

    /**
     * {@inheritDoc}
     **/
    function getIdentifierField()
    {
    }
}
