<?php

namespace Theaterjobs\MainBundle\Twig;


/**
 * Aggregation Count Extension
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class AggregationCountExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('aggregation_count', array($this, 'aggregationCount')),
        );
    }


    public function aggregationCount($aggregation, $index)
    {

        $key = array_search($index, array_column($aggregation, 'key'));


        return is_numeric($key) ? $aggregation[$key]['doc_count'] : 0;

    }
}