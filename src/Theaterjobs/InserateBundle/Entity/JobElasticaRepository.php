<?php

namespace Theaterjobs\InserateBundle\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Aggregation;
use Elastica\Filter;
use FOS\ElasticaBundle\Repository;
use Elastica\Query;
use Theaterjobs\InserateBundle\Model\JobSearch;

class JobElasticaRepository extends Repository
{

    protected function getArrayCollectionIds(ArrayCollection $arrayCollection)
    {
        return $arrayCollection->map(function ($elment) {
            return $elment->getId();
        })->getValues();
    }

    /**
     * @param $category
     * @param $status
     * @return Query
     */
    public function getPublicJobsForSubCategories($category, $status)
    {
        $query = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();
        $sortField = null;


        if (!empty($category)) {
            $boolQuery->addMust(
                new Query\Terms('categories.id', $category)
            );
        }

        if (!empty($status)) {
            $boolQuery->addMust(
                new Query\Terms('status', [$status])
            );
            // set the field that results will be sorted based on the status
            switch ($status) {
                case 1:
                    $sortField = 'publishedAt';
                    break;
                case 3:
                    $sortField = 'archivedAt';
                    $boolQuery->addMust(new Query\Range('archivedAt', ['gte' => 'now-90d']));
                    break;
            }
        }


        $boolQuery->addMust($query);

        $query = new Query($boolQuery);

        if ($sortField) {
            $query->addSort([
                $sortField => [
                    'order' => 'desc',
                    'missing' => PHP_INT_MAX - 1
                ]
            ]);
        }

        return $query;
    }

    /**
     * @return Query
     */
    public function getCategoriesAggregationQuery()
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('status', [1]));
        $query = new Query($boolQuery);
        $query->addAggregation($this->getCategoriesAggregation());

        // we don't need the search results, only statistics
        $query->setSize(0);

        return $query;
    }

    /**
     * @param $organizations
     * @return Query
     */
    public function getCategoriesAggregationQueryMembers($organizations)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('organization.slug', $organizations));
        $query = new Query($boolQuery);
        $query->addAggregation($this->getCategoriesAggregation());

        // we don't need the search results, only statistics
        $query->setSize(0);

        return $query;
    }

    private function getCategoriesAggregation()
    {
        // Simple aggregation (based on categories, we get the doc_count for each parent category)
        $categoriesAggregation = new Aggregation\Terms('categories');
        $categoriesAggregation->setField('categories.parent.slug');
        $categoriesAggregation->setSize(11);
        $categoriesAggregation->setExclude('categories-.*');

        return $categoriesAggregation;
    }

    private function getGratificationAggregation()
    {
        // Simple aggregation (based on gratification, we get the doc_count for each parent category)
        $gratificationAggregation = new Aggregation\Terms('gratification');
        $gratificationAggregation->setField('gratification.id');

        return $gratificationAggregation;
    }

    private function getSubcategoriesAggregation($subcategories)
    {
        // Simple aggregation (based on subcategories, we get the doc_count for each subcategory)
        $subcategoriesAggregation = new Aggregation\Filters('subcategories');
        foreach ($subcategories as $key => $value) {
            $term = new Filter\Term();
            $term->setTerm('categories.id', $key);
            $subcategoriesAggregation->addFilter($term);
        }

        return $subcategoriesAggregation;
    }

    /**
     * Counts Occurrence of statuses for each job
     * @return Aggregation\Terms
     *
     */
    private function getJobStatusAggregation()
    {
        $jobStatusAggregation = new Aggregation\Terms('status_count');
        $jobStatusAggregation->setField('status');

        return $jobStatusAggregation;
    }

    public function generalSearch($searchPhrase)
    {
        $query = $this->keyWordFieldSearch($searchPhrase);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addFilter(new Query\Terms('status', [1]));
        $boolQuery->addMust($query);
        $query = new Query($boolQuery);

        $query->addSort([
            'publishedAt' => [
                'order' => 'desc',
                'missing' => PHP_INT_MAX - 1
            ]
        ]);

        return $query;
    }

    protected function keywordFieldSearch($searchPhrase)
    {
        $query = new Query\MultiMatch();
        $query->setQuery($searchPhrase);
        $query->setType('best_fields');
        $query->setFields(
            [
                'title.autocomplete^3',
                'organization.name.autocomplete^1'
            ]
        );
        $query->setTieBreaker(0.3);
        $query->setMinimumShouldMatch('80%');

        return $query;
    }


    /**
     * @param JobSearch $jobSearch
     * and if true it means that will return results with the create date equal tu current date.
     * @param array $subcategories
     * @return Query|Query\Match|Query\MatchAll
     */
    public function search(JobSearch $jobSearch, $subcategories = [])
    {
        // we create a multi match query to return all the jobs when a search phrase is used
        if ($jobSearch->getSearchPhrase() != null && $jobSearch != '') {
            $query = $this->keyWordFieldSearch($jobSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        /*
            Category filter
            We add this filter only when the category is not null
        */

        if (null !== $jobSearch->getCategory()) {
            $cat = is_string($jobSearch->getCategory()) ?: $jobSearch->getCategory()->getSlug(); // Can be also a string
            $boolQuery->addMust(
                new Query\Terms('categories.parent.slug', [$cat])
            );
        }

        //Check if is collection and has elements
        if ($jobSearch->getGratification() instanceof ArrayCollection && $jobSearch->getGratification()->count() > 0) {
            $boolQuery->addMust(
                new Query\Terms('gratification.id', $this->getArrayCollectionIds($jobSearch->getGratification()))
            );
        }//The  saved search notification command will setGratification same as getArrayCollectionIds() result.
        elseif (is_array($jobSearch->getGratification()) && count($jobSearch->getGratification()) > 0) {
            $boolQuery->addMust(
                new Query\Terms('gratification.id', $jobSearch->getGratification())
            );
        }


        if (!empty($jobSearch->getJobFavourites())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobFavourites())
            );
        }


        if (!empty($jobSearch->getJobApplications())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobApplications())
            );
        }


        // check if the search has filtered multiple subcategories
        if ($jobSearch->getSubcategories()) {
            $boolQuery->addMust(
                new Query\Terms('categories.id', $jobSearch->getSubcategories())
            );
        }

        // check if the search has filtered multiple subcategories
        if (!empty($jobSearch->getOrganization())) {
            $boolQuery->addMust(
                new Query\Terms('organization.slug', [$jobSearch->getOrganization()])
            );
        }

        // check if the search has filtered multiple subcategories
        if (!empty($jobSearch->getUser())) {
            $boolQuery->addMust(
                new Query\Terms('user.id', [$jobSearch->getUser()->getId()])
            );
            $boolQuery->addMustNot(
                new Query\Exists('organization')
            );
        }


        // add geo location search
        if ($jobSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('geolocation', $jobSearch->getLocation(), $jobSearch->getArea())
            );
        }

        if ($jobSearch->getStatus()) {
            $boolQuery->addFilter(
                new Query\Terms('status', $jobSearch->getStatus())
            );
        }

        if ($jobSearch->isSavedSearch()) {
            $today = Carbon::today()->format('Y-m-d');

            $boolQuery->addMust(
                new Query\Range(
                    'publishedAt',
                    array(
                        'gte' => $today,
                        "format" => "yyyy-MM-dd"
                    )
                )
            );
        }

        $boolQueryOrganization = new Query\BoolQuery();


        $boolQuery->addMust([$boolQueryOrganization, $query]);

        $query = new Query($boolQuery);

        // we add an aggregation to our subcategories when a main category is chosen
        if (null !== $jobSearch->getCategory() && !empty($subcategories)) {
            $query->addAggregation($this->getSubcategoriesAggregation($subcategories));
        } else {
            $query->addAggregation($this->getCategoriesAggregation());
        }

        $query->addAggregation($this->getGratificationAggregation());
        $query->addAggregation($this->getJobStatusAggregation());

        // default sort
        $sortBy = [
            'publishedAt' => [
                'order' => 'desc', 'missing' => PHP_INT_MAX - 1
            ]
        ];

        // order by alphabetical asc and date desc
        if ($jobSearch->getSortBy() == 'alphabetical') {
            $sortBy = [
                'title.raw' => [
                    'order' => 'asc', 'missing' => PHP_INT_MAX - 1
                ]
            ];
        }

        $query->addSort($sortBy);

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'title.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
                'organization.name.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ]
            ]
        ]);

        return $query;
    }

    public function getPublishedJobsCron()
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('status', [1]));
        $query = new Query($boolQuery);

        return $query;
    }

    /**
     * Search jobs for a specific user
     *
     * @param JobSearch $jobSearch
     * @param array $subcategories
     * @return Query|Query\Match|Query\MatchAll
     */
    public function searchMyJob(JobSearch $jobSearch, $subcategories = [])
    {

        // we create a multi match query to return all the jobs when a search phrase is used
        if ($jobSearch->getSearchPhrase() != null && $jobSearch != '') {
            $query = $this->keyWordFieldSearch($jobSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        /*
            Category filter
            We add this filter only when the category is not null
        */

        if (null !== $jobSearch->getCategory()) {
            $boolQuery->addMust(
                new Query\Terms('categories.parent.slug', [$jobSearch->getCategory()->getSlug()])
            );
        }

        // check if collection has elements
        if ($jobSearch->getGratification()->count() > 0) {
            $boolQuery->addMust(
                new Query\Terms('gratification.id', $this->getArrayCollectionIds($jobSearch->getGratification()))
            );
        }


        if (!empty($jobSearch->getJobFavourites())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobFavourites())
            );
        }


        if (!empty($jobSearch->getJobApplications())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobApplications())
            );
        }

        // check if the search has filtered multiple subcategories
        if ($jobSearch->getSubcategories()) {
            $boolQuery->addMust(
                new Query\Terms('categories.id', $jobSearch->getSubcategories())
            );
        }

        // check if the search has filtered multiple subcategories
        if (!empty($jobSearch->getOrganization())) {
            $boolQuery->addMust(
                new Query\Terms('organization.slug', [$jobSearch->getOrganization()])
            );
        }

        if (!empty($jobSearch->getUser())) {
            $boolQuery->addMust(
                new Query\Terms('user.id', [$jobSearch->getUser()->getId()])
            );
        }

        // add geo location search
        if ($jobSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('geolocation', $jobSearch->getLocation(), $jobSearch->getArea())
            );
        }

        // Published or not filter
        if (!empty($jobSearch->getStatus())) {
            $boolQuery->addFilter(
                new Query\Terms('status', $jobSearch->getStatus())
            );
        } else {
            $boolQuery->addFilter(
                new Query\Terms('status', [1, 2, 3, 5])
            );
        }

        if (!empty($jobSearch->getCreateMode())) {
            $boolQuery->addShould(
                new Query\Terms('createMode', $jobSearch->getCreateMode())
            );
        }

        $boolQueryOrganization = new Query\BoolQuery();

        $boolQuery->addMust([$boolQueryOrganization, $query]);

        $query = new Query($boolQuery);

        // we add an aggregation to our subcategories when a main category is chosen
        if (null !== $jobSearch->getCategory() && $subcategories) {
            $query->addAggregation($this->getSubcategoriesAggregation($subcategories));
        } else {
            $query->addAggregation($this->getCategoriesAggregation());
        }
        $query->addAggregation($this->getGratificationAggregation());

        $query->addAggregation($this->getJobStatusAggregation());

        // default sort
        $sortBy = [
            'publishedAt' => [
                'order' => 'desc', 'missing' => PHP_INT_MAX - 1
            ]
        ];

        // order by alphabetical asc and date desc
        if ($jobSearch->getSortBy() == 'alphabetical') {
            $sortBy = [
                'title.raw' => [
                    'order' => 'asc', 'missing' => PHP_INT_MAX - 1
                ]
            ];
        }

        $query->addSort($sortBy);

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'title.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
                'organization.name.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ]
            ]
        ]);

        return $query;
    }

    public function getPublishedJobs($range = null)
    {
        $queryMatch = new Query\Match();
        $queryMatch->setField('status', 1);

        if ($range) {
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMust(
                new Query\Range('publishedAt', [
                        'gte' => $range->format('Y-m-d'),
                        "format" => "yyyy-mm-dd"
                    ]
                )
            );
            $boolQuery->addMust($queryMatch);
            $query = new Query($boolQuery);
        } else {
            $query = new Query($queryMatch);
        }

        $query->setSize(0);
        return $query;
    }

    /**
     * @param JobSearch $jobSearch
     * @param array $subcategories
     * @return Query|Query\Match|Query\MatchAll
     */
    public function searchForMember(JobSearch $jobSearch, $subcategories = [])
    {

        // we create a multi match query to return all the jobs when a search phrase is used
        if ($jobSearch->getSearchPhrase() != null && $jobSearch != '') {
            $query = $this->keyWordFieldSearch($jobSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        /*
            Category filter
            We add this filter only when the category is not null
        */

        if (null !== $jobSearch->getCategory()) {
            $boolQuery->addMust(
                new Query\Terms('categories.parent.slug', [$jobSearch->getCategory()->getSlug()])
            );
        }

        // check if collection has elements
        if ($jobSearch->getGratification()->count() > 0) {
            $boolQuery->addMust(
                new Query\Terms('gratification.id', $this->getArrayCollectionIds($jobSearch->getGratification()))
            );
        }


        if (!empty($jobSearch->getJobFavourites())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobFavourites())
            );
        }


        // check if the search has filtered multiple subcategories
        if ($jobSearch->getSubcategories()) {
            $boolQuery->addMust(
                new Query\Terms('categories.id', $jobSearch->getSubcategories())
            );
        }


        if (!empty($jobSearch->getJobApplications())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobApplications())
            );
        }

        if ($jobSearch->getOrganization()) {
            $boolQuery->addMust(new Query\Terms('organization.slug', [$jobSearch->getOrganization()]));
        }

        // add geo location search
        if ($jobSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('geolocation', $jobSearch->getLocation(), $jobSearch->getArea())
            );
        }

        // Published or not filter
        if (!empty($jobSearch->getStatus())) {
            $boolQuery->addFilter(
                new Query\Terms('status', $jobSearch->getStatus())
            );
        }

        $boolQueryOrganization = new Query\BoolQuery();


        $boolQuery->addMust([$boolQueryOrganization, $query]);

        $query = new Query($boolQuery);

        // we add an aggregation to our subcategories when a main category is chosen
        if (null !== $jobSearch->getCategory() && $subcategories) {
            $query->addAggregation($this->getSubcategoriesAggregation($subcategories));
        } else {
            $query->addAggregation($this->getCategoriesAggregation());
        }
        $query->addAggregation($this->getGratificationAggregation());

        //Add counts for job statuses
        $query->addAggregation($this->getJobStatusAggregation());

        // default sort
        $sortBy = [
            'publishedAt' => [
                'order' => 'desc', 'missing' => PHP_INT_MAX - 1
            ]
        ];

        // order by alphabetical asc and date desc
        if ($jobSearch->getSortBy() == 'alphabetical') {
            $sortBy = [
                'title.raw' => [
                    'order' => 'asc', 'missing' => PHP_INT_MAX - 1
                ]
            ];
        }

        $query->addSort($sortBy);

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'title.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
                'organization.name.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ]
            ]
        ]);

        return $query;
    }

    /**
     * @param JobSearch $jobSearch
     * @param array $subcategories
     * @return Query|Query\Match|Query\MatchAll
     * @internal param JobSearch $jobSearch
     */
    public function searchForUser(JobSearch $jobSearch, $subcategories = [])
    {
        // we create a multi match query to return all the jobs when a search phrase is used
        if ($jobSearch->getSearchPhrase() != null && $jobSearch != '') {
            $query = $this->keyWordFieldSearch($jobSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        /*
            Category filter
            We add this filter only when the category is not null
        */

        if (null !== $jobSearch->getCategory()) {
            $boolQuery->addMust(
                new Query\Terms('categories.parent.slug', [$jobSearch->getCategory()->getSlug()])
            );
        }

        // check if collection has elements
        if ($jobSearch->getGratification()->count() > 0) {
            $boolQuery->addMust(
                new Query\Terms('gratification.id', $this->getArrayCollectionIds($jobSearch->getGratification()))
            );
        }


        if (!empty($jobSearch->getJobFavourites())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobFavourites())
            );
        }


        if (!empty($jobSearch->getJobApplications())) {
            $boolQuery->addMust(
                new Query\Terms('id', $jobSearch->getJobApplications())
            );
        }

        // check if the search has filtered multiple subcategories
        if ($jobSearch->getSubcategories()) {
            $boolQuery->addMust(
                new Query\Terms('categories.id', $jobSearch->getSubcategories())
            );
        }

        // check if the search has filtered multiple subcategories
        if (!empty($jobSearch->getOrganization())) {
            $boolQuery->addMust(
                new Query\Terms('organization.slug', [$jobSearch->getOrganization()])
            );
        }

        // add geo location search
        if ($jobSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('geolocation', $jobSearch->getLocation(), $jobSearch->getArea())
            );
        }


        if ($jobSearch->isFavorite()) {
            $boolQuery->addMust(new Query\Terms('status', [1, 3, 2]));
        } else {
            $boolQuery->addMust(new Query\Terms('status', [1]));
        }


        $boolQueryOrganization = new Query\BoolQuery();


        $boolQuery->addMust([$boolQueryOrganization, $query]);

        $query = new Query($boolQuery);

        // we add an aggregation to our subcategories when a main category is chosen
        if (null !== $jobSearch->getCategory() && !empty($subcategories)) {
            $query->addAggregation($this->getSubcategoriesAggregation($subcategories));
        } else {
            $query->addAggregation($this->getCategoriesAggregation());
        }
        $query->addAggregation($this->getGratificationAggregation());

        $query->addAggregation($this->getJobStatusAggregation());

        // default sort
        $sortBy = [
            'publishedAt' => [
                'order' => 'desc', 'missing' => PHP_INT_MAX - 1
            ]
        ];

        // order by alphabetical asc and date desc
        if ($jobSearch->getSortBy() == 'alphabetical') {
            $sortBy = [
                'title.raw' => [
                    'order' => 'asc', 'missing' => PHP_INT_MAX - 1
                ]
            ];
        }

        $query->addSort($sortBy);

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'title.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
                'organization.name.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ]
            ]
        ]);

        return $query;
    }


    /**
     * @param $id
     * @return Query $query
     *
     * All jobs of a specified user
     * Query build for elastic search
     *
     */
    public function userJobs($id)
    {
        $query = new Query\Match();
        $query->setFieldMinimumShouldMatch('user.id', '100%');
        $query->setFieldQuery('user.id', $id);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Query\Terms('user.id', [$id])
        );

        $boolQuery->addMustNot(
            new Query\Exists('organization')
        );

        $boolQuery->addFilter(
        //Status
        //Published, pending for publish
            new Query\Terms('status', [1, 2, 3, 5])
        );

        $boolQuery->addMust($query);

        $query = new Query($boolQuery);

        return $query;
    }


    /**
     * Get last jobs of 10 days
     * @param $container fos_elastica.index.theaterjobs.job
     * @param JobSearch $jobSearch
     * @return Query\BoolQuery
     */
    public function newJobs($container, JobSearch $jobSearch)
    {
        $now = Carbon::now();
        $tenDaysAgo = $now->subDays(10);

        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust(
            new Query\Range('publishedAt', [
                    'gte' => $tenDaysAgo->format('Y-m-d'),
                    "format" => "yyyy-mm-dd"
                ]
            )
        );

        if ($jobSearch->getStatus()) {
            $boolQuery->addMust(
                new Query\Terms('status', $jobSearch->getStatus())
            );
        }
        $query = new Query($boolQuery);

        return $container->search($query)->count();
    }

    /**
     * get latest jobs query
     */
    public function latestJobs()
    {
        $queryMatch = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($queryMatch);
        $boolQuery->addMust(new Query\Terms('status', [1]));

        $query = new Query($boolQuery);
        $query->addSort([
                'createdAt' => [
                    'order' => 'desc'
                ]
            ]
        );

        return $query;
    }

    /**
     * Get all published educations of this user
     *
     * @param $userId
     * @param bool $organizationName //Set to true if we wat to archive education offers by user in name of organizations.
     * @return Query
     */
    public function getPublishedEducationsByUser($userId, $organizationName = false)
    {
        $queryMatch = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($queryMatch);
        $boolQuery->addMust(new Query\Terms('status', [1]));
        $boolQuery->addMust(new Query\Terms('gratification.id', [6, 7, 8]));

        //If is not required to archive jobs of user also in organization name , we archive only in user's name.
        if (!$organizationName) {
            $boolQuery->addMust(new Query\Terms('createMode', [2]));
        }

        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));

        $query = new Query($boolQuery);
        return $query;
    }
    /**
     * Get all published educations of this user
     *
     * @param $userId
     * @return Query
     */
    public function getPublishRequestsForUser($userId)
    {
        $queryMatch = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust($queryMatch);
        $boolQuery->addMust(new Query\Terms('status', [Job::STATUS_PENDING]));
        $boolQuery->addMust(new Query\Terms('user.id', [$userId]));

        $query = new Query($boolQuery);
        return $query;
    }

    /**
     * Find published jobs that their publication period has ended
     * @param $publicationEndDate
     * @param $seen
     * @return Query
     *
     */
    public function expiredPublishedJobs($publicationEndDate, $seen = false)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMustNot(new Query\Exists('archivedAt'));
        $boolQuery->addMust(new Query\Range('publicationEnd', [
            'lt' => $publicationEndDate,
            'time_zone' => '+02:00',
            'format' => 'yyyy-MM-dd'
        ]));
        if ($seen) {
            $boolQuery->addMust(new Query\Terms('seen', [1]));
        }
        $query = new Query($boolQuery);
        return $query;
    }

}
