<?php

namespace Theaterjobs\InserateBundle\Entity;

use Elastica\Aggregation;
use FOS\ElasticaBundle\Repository;
use Theaterjobs\InserateBundle\Model\OrganizationSearch;
use Elastica\Query;

class OrganizationElasticaRepository extends Repository
{

    protected function getOrganizationKindAggregation()
    {

        $organizationKindAggregation = new Aggregation\Terms('organizationKind');
        $organizationKindAggregation->setField('organizationKind.id');

        return $organizationKindAggregation;
    }

    protected function getOrganizationSectionAggregation()
    {
        $organizationSectionAggregation = new Aggregation\Terms('organizationSection');
        $organizationSectionAggregation->setField('organizationSection.id');

        return $organizationSectionAggregation;
    }

    /**
     * Counts Occurrence of statuses for each organization
     * @return Aggregation\Terms
     *
     */
    protected function getOrganizationStatusAggregation()
    {
        $organizationStatusAggregation = new Aggregation\Terms('status_count');
        $organizationStatusAggregation->setField('status');

        return $organizationStatusAggregation;
    }

    /**
     * Create aggregations with UserOrganizations
     *
     * @return Aggregation\Terms
     */
    protected function getUserOrganizationsAggregation()
    {
        $userOrganizationAggregation = new Aggregation\Terms('userOrganizations');
        $userOrganizationAggregation->setField('userOrganizations.user.id');

        return $userOrganizationAggregation;
    }

    protected function populateArrayFromCollection($arrayCollection)
    {
        return array_map(function ($element) {
            return $element->getId();
        },
            $arrayCollection->toArray()
        );
    }

    public function generalSearch($searchPhrase, $isAdmin = false)
    {
        $query = new Query\Match();
        $query->setFieldMinimumShouldMatch('name.autocomplete', '80%');
        $query->setFieldQuery('name.autocomplete', $searchPhrase);

        // set default status
        $status = [Organization::ACTIVE, Organization::CLOSED];
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addFilter(new Query\Terms('isVisibleInList', [1]));
        $boolQuery->addFilter(new Query\Terms('status', $status));
        $boolQuery->addMust($query);
        $query = new Query($boolQuery);

        return $query;
    }

    public function getActiveOrganizations()
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('status', [2]));
        $boolQuery->addMust(new Query\Terms('isVisibleInList', [1]));
        $query = new Query($boolQuery);

        return $query;
    }

    public function getOrganizationBySlug($slug)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('slug', [$slug]));

        $query = new Query($boolQuery);

        $query->setSize(1);

        return $boolQuery;
    }

    public function search(OrganizationSearch $organizationSearch)
    {

        // we create a multi match query to return all the organizations when a search phrase is used
        if ($organizationSearch->getSearchPhrase() != null && $organizationSearch != '') {
            $query = new Query\Match();
            $query->setFieldMinimumShouldMatch('name.autocomplete', '80%');
            $query->setFieldQuery('name.autocomplete', $organizationSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        // add geo location search
        if ($organizationSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('geolocation', $organizationSearch->getLocation(), $organizationSearch->getArea())
            );
        }

        if ($organizationSearch->getOrganizationFavourites()) {
            $boolQuery->addMust(
                new Query\Terms('id', $organizationSearch->getOrganizationFavourites())
            );
        }

        if ($organizationSearch->isOrganization()) {
            $boolQuery->addMust(
                new Query\Terms('id', $organizationSearch->getMyOrganizations())
            );
        }


        if ($organizationSearch->isDefaultStatus() && !$organizationSearch->isFavorite()) {
            $boolQuery->addFilter(new Query\Terms('isVisibleInList', [1]));
        }

        // Admin specified status
        if ($organizationSearch->getStatus()) {
            $boolQuery->addFilter(
                new Query\Terms('status', $organizationSearch->getStatus())
            );
            // User default statuses
        } elseif ($organizationSearch->isDefaultStatus()) {
            if ($organizationSearch->isFavorite() || $organizationSearch->isOrganization()) {
                $boolQuery->addFilter(new Query\Terms('status', [2, 3, 4]));
            } else {
                $boolQuery->addFilter(new Query\Terms('status', [2, 4]));
            }
        }

        $boolQueryFilters = new Query\BoolQuery();

        if (count($organizationSearch->getOrganizationKind()) != 0) {

            $organizationKinds = $this->populateArrayFromCollection($organizationSearch->getOrganizationKind());
            foreach ($organizationKinds as $item) {
                $boolQueryFilters->addMust(
                    new Query\Terms('organizationKind.id', [$item])
                );
            }
        }

        if (count($organizationSearch->getOrganizationSection()) != 0) {

            $organizationSections = $this->populateArrayFromCollection($organizationSearch->getOrganizationSection());
            foreach ($organizationSections as $item) {
                $boolQueryFilters->addMust(
                    new Query\Terms('organizationSection.id', [$item])
                );
            }

        }

        //Check for userOrganization filter
        if (!empty($organizationSearch->getForUser())) {

            $boolQueryFilters->addMust(
                new Query\Terms('userOrganizations.user.id', array($organizationSearch->getForUser()->getId()))
            );
            $boolQuery->addMustNot(
                new Query\Exists('userOrganizations.revokedAt')
            );
        }

        $boolQuery->addMust($boolQueryFilters);


        // check if the search has filtered tags
        if ($organizationSearch->getTags()) {
            $boolQuery->addMust(
                new Query\Terms('organizationStage.tags.title', explode(",", $organizationSearch->getTags()))
            );
        }

        $boolQuery->addMust($query);

        $query = new Query($boolQuery);


        // order by alphabetical asc
        if ($organizationSearch->getsortChoices() == 'alphabetical') {
            $query->addSort([
                    'name.raw' => [
                        'order' => 'asc'
                    ]
                ]
            );
        }

        if ($organizationSearch->getSearchPhrase() == null) {
            $query->addSort([
                    'updatedAt' => [
                        'order' => 'desc'
                    ]
                ]
            );
        }

        $query->addAggregation($this->getOrganizationKindAggregation());

        $query->addAggregation($this->getOrganizationSectionAggregation());

        $query->addAggregation($this->getOrganizationStatusAggregation());

        if ($organizationSearch->getForUser()) {
            $query->addAggregation($this->getUserOrganizationsAggregation());
        }

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'name.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                    "fragmenter" => "span"
                ]
            ]
        ]);

        return $query;
    }

    /**
     * Query for published related jobs of an organization
     * @param $organizationId
     * @return Query
     */
    public function publishedRelatedJobs($organizationId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('organization.id', [$organizationId]));
        $boolQuery->addMust(new Query\Terms('status', [1]));
        $query = new Query($boolQuery);
        return $query;
    }


    /**
     * Query for related jobs of an organization
     * @param $organizationId
     * @return Query
     */
    public function relatedJobs($organizationId)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addShould(new Query\Terms('organization.id', [$organizationId]));
        $statusAggregation = new Aggregation\Terms('status');
        $statusAggregation->setField('status');
        $statusAggregation->setSize(5);
        $query = new Query($boolQuery);
        $query->addAggregation($statusAggregation);

        return $query;
    }

    /**
     * Query for related people of an organization
     * @param $organizationId
     * @param $isAdmin
     * @return Query
     */
    public function relatedPeople($organizationId, $isAdmin)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addShould(new Query\Terms('experience.organization.id', [$organizationId]));
        $boolQuery->addShould(new Query\Terms('productionParticipations.production.organizationRelated.id', [$organizationId]));
        $boolQuery->addShould(new Query\Terms('qualificationSection.qualifications.organizationRelated.id', [$organizationId]));
        $base = new Query\BoolQuery();

        if ($isAdmin) {
            $base->addMust([$boolQuery]);
        } else {
            $boolQueryUser = new Query\BoolQuery();
            $boolQueryUser->addMust(new Query\Terms('isPublished', [1]));
            $base->addMust([$boolQuery, $boolQueryUser]);
        }

        $query = new Query($base);

        $query->setSize(0);

        return $query;
    }

}
