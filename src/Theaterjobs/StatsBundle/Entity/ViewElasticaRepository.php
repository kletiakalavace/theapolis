<?php

namespace Theaterjobs\StatsBundle\Entity;


use Elastica\Query;
use Elastica\Aggregation;
use FOS\ElasticaBundle\Repository;

/**
 * Class ViewEalasticaRepository
 * @package Theaterjobs\StatsBundle\Entity
 */
class ViewElasticaRepository extends Repository
{
    /**
     * Returns entity views(views for each record) grouped by id and count
     * [[id, count]...]
     *
     * @param $entityName
     * @param $since string format date
     * @return Query|Query\Match
     */
    public function getViewsByEntity($entityName, $since)
    {
        $objectClassAggregation = new Aggregation\Terms('objectClass');
        $objectClassAggregation->setField('foreignKey');

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('objectClass', [$entityName]));
        $boolQuery->addMust(new Query\Range('createdAt', ['gt' => $since]));

        $query = new Query($boolQuery);
        $query->addAggregation($objectClassAggregation);

        return $query;
    }

    /**
     * Get views for a single entity(single record)
     *
     * @param $entityName
     * @param $id
     * @param $since
     * @return Query
     */
    public function getEntityViews($entityName, $id, $since = null)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('objectClass', [$entityName]));
        $boolQuery->addMust(new Query\Terms('foreignKey', is_array($id) ? $id : [$id]));

        if ($since) {
            $boolQuery->addMust(new Query\Range('createdAt', ['gt' => $since]));
        }

        $query = new Query($boolQuery);
        $query->setSize(0);

        return $query;
    }
}