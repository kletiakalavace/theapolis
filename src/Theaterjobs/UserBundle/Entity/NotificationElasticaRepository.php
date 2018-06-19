<?php

namespace Theaterjobs\UserBundle\Entity;

use Elastica\Query;
use FOS\ElasticaBundle\Repository;

class NotificationElasticaRepository extends Repository
{
    /**
     * get latest jobs query
     */
    public function latestJobs()
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('status', [1]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }

    /**
     * Notification Required Action
     * @param $userId
     *
     * @return Query
     */
    public function unseenNRA($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));
        $boolQuery->addMust(new Query\Terms('requireAction', [1]));
        $boolQuery->addMust(new Query\Terms('seen', [0]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }

    /**
     * Notification Required Action
     * @param $userId
     *
     * @return Query
     */
    public function allNRA($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));
        $boolQuery->addMust(new Query\Terms('requireAction', [1]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }

    /**
     * Notification Informative
     * @param integer $userId
     * @return Query
     */
    public function unseenNI($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));
        $boolQuery->addMust(new Query\Terms('requireAction', [0]));
        $boolQuery->addMust(new Query\Terms('seen', [0]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }

    /**
     * Notification Informative
     * @param integer $userId
     * @return Query
     */
    public function allNI($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));
        $boolQuery->addMust(new Query\Terms('requireAction', [0]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }
    /**
     * Notification Informative
     * @param integer $userId
     * @return Query
     */
    public function allNotifications($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }

    /**
     * Notification Informative
     * @param integer $userId
     * @return Query
     */
    public function allUnseenNotifications($userId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));
        $boolQuery->addMust(new Query\Terms('seen', [0]));

        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'desc']]);

        return $query;
    }
}