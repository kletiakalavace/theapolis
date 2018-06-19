<?php

namespace Theaterjobs\UserBundle\Entity;


use FOS\ElasticaBundle\Repository;
use Elastica\Query;

class UserActivityElasticaRepository extends Repository
{
    /**
     * Get activity logs for an entity
     * @param $entityName
     * @param $id
     * @return Query
     */
    public function getEntityActivity($entityName, $id)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('entityId', [$id]));

        $boolQuery->addFilter(new Query\Terms('entityClass', [$entityName]));

        $query = new Query($boolQuery);

        $query->addSort([
            'createdAt' => [
                'order' => 'desc',
                'missing' => PHP_INT_MAX - 1
            ],
        ]);

        return $query;

    }

    /**
     * Get activity only for userOrganization since wants 2 entity $ids
     * @param $organizationId
     * @param $userId
     * @return Query
     */
    public function getUserOrganizationActivity($organizationId, $userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('entityId', [$organizationId]));
        $boolQuery->addMust(new Query\Terms('user', [$userId]));

        $boolQuery->addFilter(new Query\Terms('entityClass', [UserOrganization::class]));

        $query = new Query($boolQuery);

        $query->addSort([
            'createdAt' => [
                'order' => 'desc',
                'missing' => PHP_INT_MAX - 1
            ],
        ]);

        return $query;

    }

}