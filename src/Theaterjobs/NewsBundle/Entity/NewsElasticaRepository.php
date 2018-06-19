<?php

namespace Theaterjobs\NewsBundle\Entity;

use Theaterjobs\NewsBundle\Model\NewsSearch;
use FOS\ElasticaBundle\Repository;
use Elastica\Query;

class NewsElasticaRepository extends Repository
{

    public function generalSearch($searchPhrase, $isAdmin = false)
    {
        $query = $this->keyWordFieldSearch($searchPhrase);
        $boolQuery = new Query\BoolQuery();
        if (!$isAdmin) {

            $boolQuery->addMust(
                new Query\Terms('published', [1])
            );
            $boolQuery->addMust([$query]);

        }
        $query = new Query($boolQuery);

        $query->addSort(
            [
                'publishAt' =>
                    [
                        'order' => 'desc',
                        'missing' => PHP_INT_MAX - 1
                    ]
            ]
        );

        return $query;
    }

    public function searchApplication($arguments = [])
    {
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust(
            new Query\Terms('organizations.id', [$arguments['id']])
        );
        $boolQuery->addMust(
            new Query\Terms('tags.title', [$arguments['tag']])
        );

        return $boolQuery;
    }

    public function relatedNews($organizationId)
    {
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust(new Query\Terms('organizations.id', [$organizationId]));
        $boolQuery->addMust(new Query\Terms('published', [1]));

        $query = new Query($boolQuery);

        $query->setSize(0);

        return $query;
    }

    protected function keywordFieldSearch($searchPhrase)
    {
        $query = new Query\MultiMatch();

        $query->setType('best_fields');
        $query->setQuery($searchPhrase);
        $query->setFields(
            [
                'title.autocomplete^8',
                'pretitle.autocomplete^5',
                'shortDescription.autocomplete^3',
                'organizations.name.autocomplete^1'
            ]
        );
        $query->setTieBreaker(0.3);
        $query->setMinimumShouldMatch('80%');

        return $query;
    }

    public function search(NewsSearch $newsSearch)
    {
        // we create a multi match query to return all the news when a search phrase is used
        if ($newsSearch->getSearchPhrase() != null && $newsSearch != '') {
            $query = $this->keyWordFieldSearch($newsSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        // check if the search has filtered tags
        if ($newsSearch->getTags()) {
            $boolQuery->addMust(
                new Query\Terms('tags.title', explode(",", $newsSearch->getTags()))
            );
        }

        if ($newsSearch->getNewsFavourites()) {
            $boolQuery->addMust(
                new Query\Terms('id', $newsSearch->getNewsFavourites())
            );
        }

        if ($newsSearch->getOrganization() != null) {
            $boolQuery->addMust(
                new Query\Terms('organizations.slug', [$newsSearch->getOrganization()])
            );
        }

        if ($newsSearch->getYears() !== null) {
            $boolQuery->addMust(
                new Query\Range('publishAt',
                    [
                        "from" => $newsSearch->getYears(),
                        "to" => $newsSearch->getYears() . "||/y", "format" => "yyyy",
                        //@todo must check os and php time_zone both of them must match (utc)
                        'time_zone' => '+02:00'
                    ]
                )
            );
        }

        $boolQuery->addFilter(
            new Query\Terms('published', [(int)$newsSearch->isPublished()])
        );


        $boolQuery->addMust($query);
        $query = new Query($boolQuery);

        $query->addSort(
            [
                'publishAt' =>
                    [
                        'order' => 'desc',
                        'missing' => PHP_INT_MAX - 1
                    ]
            ]
        );

        $query->setHighlight([
                'pre_tags' => ['<em class="search-text">'],
                'post_tags' => ['</em>'],
                'order' => 'score',
                'fields' =>
                    [
                        '*title.autocomplete' =>
                            [
                                'fragment_size' => 200,
                                'number_of_fragments' => 1,
                            ],
                        '*escription.autocomplete' =>
                            [
                                'fragment_size' => 200,
                                'number_of_fragments' => 1,
                            ],
                    ]
            ]
        );


        return $query;
    }

    public function getPublishedNews()
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('published', [1]));
        $query = new Query($boolQuery);

        return $query;
    }

    /**
     * get latest news query
     * @return Query
     */
    public function latestNews()
    {
        $queryMatch = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($queryMatch);
        $boolQuery->addMust(new Query\Terms('published', [1]));

        $query = new Query($boolQuery);
        $query->addSort([
            'publishAt' => ['order' => 'desc', 'missing' => PHP_INT_MAX - 1],
            'updatedAt' => ['order' => 'desc', 'missing' => PHP_INT_MAX - 1]
        ]);

        return $query;
    }

}