<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Elastica\Query;
use FOS\ElasticaBundle\Repository;
use Theaterjobs\AdminBundle\Model\ProductionSearch;
use Theaterjobs\MainBundle\Utility\ESNgramFilterTrait;

/**
 * Class ProductionElasticaRepository
 * @package Theaterjobs\ProfileBundle\Entity
 */
class ProductionElasticaRepository extends Repository
{
    use ESNgramFilterTrait;

    /**
     * @param ProductionSearch $productionSearch
     * @return Query
     */
    public function search(ProductionSearch $productionSearch)
    {

        if ($productionSearch->getInput()) {
            $queryMatch = new Query\MultiMatch();
            $queryMatch->setQuery($productionSearch->getInput());
            $queryMatch->setType('best_fields');
            $queryMatch->setFields([
                'name.ngram^8',
                'creators.name.ngram^6',
                'directors.name.ngram^6',
                'organizationRelated.name.ngram^4',
                'year^2'
            ]);
            $queryMatch->setOperator('and');
            $queryMatch->setTieBreaker(0.3);
            $queryMatch->setMinimumShouldMatch('100%');
            $query = new Query($queryMatch);
        } elseif ($productionSearch->getName()) {
            $query = $this->matchQuery('name.ngram', $productionSearch->getName());
        } elseif ($productionSearch->getCreator()) {
            $query = $this->matchQuery('creators.name.ngram', $productionSearch->getCreator());
        } elseif ($productionSearch->getDirector()) {
            $query = $this->matchQuery('directors.name.ngram', $productionSearch->getDirector());
        } elseif ($productionSearch->getOrganization()) {
            $query = $this->matchQuery('organizationRelated.name.ngram', $productionSearch->getOrganization());
        } elseif ($productionSearch->getYear()) {
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMust(
                new Query\Term(['year' => $productionSearch->getYear()])
            );
            $query = new Query($boolQuery);
        } else {
            $queryMatchAll = new Query\MatchAll();
            $query = new Query($queryMatchAll);
        }

        if (is_numeric($productionSearch->getStatus())) {
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addFilter(
                new Query\Terms('checked', [$productionSearch->getStatus()])
            );
            $query->setPostFilter($boolQuery);
        }

        if ($productionSearch->getOrder() && $productionSearch->getOrderCol()) {
            $column = null;

            switch ($productionSearch->getOrderCol()) {
                case 'name':
                    $column = 'name.raw';
                    break;
                case 'creators':
                    $column = 'creators.name.raw';
                    break;
                case 'directors':
                    $column = 'directors.name.raw';
                    break;
                case 'organization':
                    $column = 'organizationRelated.name.raw';
                    break;
                case 'year':
                    $column = 'year';
                    break;

            }

            if ($column) {
                $query->addSort(
                    [
                        $column =>
                            [
                                'order' => $productionSearch->getOrder(),
                                'missing' => PHP_INT_MAX - 1
                            ]
                    ]
                );
            }
        }

        return $query;

    }

    /**
     * @param $directorId
     * @return Query
     */
    public function getDirectorProductions($directorId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Query\Term(['directors.id' => $directorId])
        );

        $query = new Query($boolQuery);

        $query->addSort(['id' => ['order' => 'DESC']]);

        return $query;
    }

    /**
     * @param $creatorId
     * @return Query
     */
    public function getCreatorProductions($creatorId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Query\Term(['creators.id' => $creatorId])
        );

        $query = new Query($boolQuery);

        $query->addSort(['id' => ['order' => 'DESC']]);

        return $query;
    }
}