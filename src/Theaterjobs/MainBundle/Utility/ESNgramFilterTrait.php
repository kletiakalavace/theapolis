<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 06/05/2018
 * Time: 11:35
 */

namespace Theaterjobs\MainBundle\Utility;

use Elastica\Query;


trait ESNgramFilterTrait
{

    /**
     * @param  string $fieldName
     * @param $value
     * @return Query
     */
    protected function matchQuery($fieldName, $value)
    {
        $queryMatch = new Query\Match();
        $queryMatch->setFieldQuery($fieldName, $value);

        $queryMatch->setFieldOperator($fieldName, 'and');
        $queryMatch->setFieldMinimumShouldMatch($fieldName, '100%');

        return new Query($queryMatch);
    }

}