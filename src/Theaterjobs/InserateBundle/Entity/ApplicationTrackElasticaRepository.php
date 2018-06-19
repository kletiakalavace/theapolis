<?php

namespace Theaterjobs\InserateBundle\Entity;

use FOS\ElasticaBundle\Repository;
use Elastica\Query;

/**
 * Class ApplicationTrackElasticaRepository
 * @package Theaterjobs\InserateBundle\Entity
 */
class ApplicationTrackElasticaRepository extends Repository
{
    /**
     * @param $id integer profile id
     * @return Query $query
     *
     * Nr of applied jobs of a specified user
     * Query build for elastic search
     *
     */
    public function countAppliedJobs($id)
    {
        $queryAll = new Query\MatchAll();

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Query\Terms('profile.id', [$id])
        );
        $boolQuery->addMust($queryAll);

        $query = new Query($boolQuery);

        $query->setSize(0);

        return $query;
    }
}