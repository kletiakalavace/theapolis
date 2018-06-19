<?php

namespace Theaterjobs\MessageBundle\Entity;

use Elastica\Query;
use FOS\ElasticaBundle\Repository;
use Elastica\Aggregation;

/**
 * Class ThreadElasticaRepository
 * @package Theaterjobs\MessageBundle\Entity
 */
class ThreadElasticaRepository extends Repository
{
    /**
     * Search all threads by subject or first|last|profile name
     *
     * @param $q
     * @param $user
     *
     * @return Query|Query\Match
     */
    public function searchThreads($q, $user)
    {
        if ($q != "") {
            //Match subject or firsrt|last|subtitle name
            $query = new Query\MultiMatch();

            $query->setType('best_fields');
            $query->setQuery($q);
            $query->setFields([
                'subject.autocomplete^4',
                'metadata.participant.profile.subtitle^3',
                'metadata.participant.profile.firstName^2',
                'metadata.participant.profile.lastName^1'
            ]);
            $query->setTieBreaker(0.3);
            $query->setMinimumShouldMatch('75%');
        } else {
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();
        //Match the user threads
        $boolQuery->addMust(
            new Query\Terms('metadata.participant.id', [$user->getId()])
        );

        //Must not be deleted
        $boolQuery->addMust(
            new Query\Terms('metadata.isDeleted', [false])
        );
        $boolQuery->addMust($query);

        $query = new Query($boolQuery);
        $query->addSort(['messages.createdAt' => ['order' => 'asc']]);

        return $query;
    }

    /**
     * Search all threads by first|last|profile name
     *
     * @param $q
     * @param $userId
     * @return Query|Query\Match
     */
    public function searchThreadsByParticipant($q, $userId)
    {
        if ($q != "") {
            //Match User first|last|subtitle name
            $query = new Query\MultiMatch();

            $query->setType('best_fields');
            $query->setQuery($q);
            $query->setFields([
                'metadata.participant.profile.firstName^3',
                'metadata.participant.profile.lastName^3'
            ]);
            $query->setTieBreaker(0.3);
            $query->setMinimumShouldMatch('80%');
        } else {
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();
        //Match the user threads
        $boolQuery->addMust(
            new Query\Terms('metadata.participant.id', [$userId])
        );

        //Must not be deleted
        $boolQuery->addMust(
            new Query\Terms('metadata.isDeleted', [0])
        );
        $boolQuery->addMust($query);

        $query = new Query($boolQuery);
        $query->addAggregation($this->getMessagesAggregation());
        $query->addSort([
            'messages.createdAt' => [
                'order' => 'desc'
            ]
        ]);

        return $query;
    }

    /**
     * Search all threads by subject
     *
     * @param $q
     * @param $user
     *
     * @return Query|Query\Match
     */
    public function searchThreadsBySubject($q, $user)
    {
        if ($q != "") {
            //Match subject
            $query = new Query\Match();
            $query->setFieldMinimumShouldMatch('subject.autocomplete', '100%');
            $query->setFieldQuery('subject.autocomplete', $q);
        } else {
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();
        //Match the user threads
        $boolQuery->addMust(
            new Query\Terms('metadata.participant.id', [$user->getId()])
        );

        //Must not be deleted
        $boolQuery->addMust(
            new Query\Terms('metadata.isDeleted', [0])
        );
        $boolQuery->addMust($query);

        $query = new Query($boolQuery);
        $query->addAggregation($this->getMessagesAggregation());
        $query->addSort(['messages.createdAt' => ['order' => 'DESC']]);

        return $query;
    }

    private function getMessagesAggregation()
    {
        // Simple aggregation (based on gratification, we get the doc_count for each parent category)
        $messagesAggregation = new Aggregation\Terms('messages');
        $messagesAggregation->setField('messages.id');

        return $messagesAggregation;
    }
}