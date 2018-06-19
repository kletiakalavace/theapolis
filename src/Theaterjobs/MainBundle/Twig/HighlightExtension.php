<?php

namespace Theaterjobs\MainBundle\Twig;

use Elastica\Result;

/**
 * Highlight ElasticSearch Results
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class HighlightExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('highlight', array($this, 'highlightFilter')),
        );
    }

    /**
     * @param Result $result
     * @param $fieldName
     * @param null $nested
     * // deep level of mappings
     * @param int $deep
     * @return \Twig_Markup
     */
    public function highlightFilter($result, $fieldName, $nested = null, $deep = 0)
    {
        // get elastica highlights
        $highlightArray = $result->getHighlights();

        $fieldNameArray = explode('.', $fieldName);


        if (array_key_exists($fieldName, $highlightArray)) {
            $raw = $highlightArray["$fieldName"][0];
        } else {
            if ($nested) {
                // get the origin field
                $raw = $nested[$fieldNameArray[$deep]];
            } else {
                // get the origin field
                $raw = $result->$fieldNameArray[$deep];
            }
        }
        return new \Twig_Markup(
            $raw,
            'utf8'
        );

    }
}