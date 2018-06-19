<?php

namespace Theaterjobs\MainBundle\Entity;

use Elastica\Aggregation;
use Elastica\Filter;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;
use Theaterjobs\InserateBundle\Entity\Job;

/**
 * SaveSearchElasticaRepository
 *
 */
class SaveSearchElasticaRepository extends Repository
{

    /**
     * Find save searches of a profile
     * @param $id
     * @return Query
     */
    public function searchesByPeopleId($id)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('profile.id', [$id]));
        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);
        return $query;
    }


    /**
     * Get save searches by people id
     * @param $id
     * @return Query
     */
    public function jobSearchesByPeopleId($id)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('profile.id', [$id]));
        $boolQuery->addMust(new Query\Terms('entity', [SaveSearch::VALID_ENTITIES['job']]));
        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);
        return $query;
    }

    public function searchByUrl($id, $url)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('profile.id', [$id]));
        $boolQuery->addMust(new Query\Terms('url', [$url]));
        $query = new Query($boolQuery);
        return $query;
    }

    /**
     * We get all the saved searches that the notification interval is set to daily.
     * @return Query|Query\Match
     */
    public function getDailyNotifiable()
    {
        $queryBool = new Query\BoolQuery();
        $queryBool->addMust(new Query\Term(['notification' => 1]));
        $queryBool->addMust(new Query\Term(['entity' => Job::class]));

        $query = new Query($queryBool);
        return $query;
    }
}
