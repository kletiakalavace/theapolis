<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Elastica\Aggregation;
use Elastica\Filter;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;
use Theaterjobs\AdminBundle\Model\AdminPeopleSearch;
use Theaterjobs\ProfileBundle\Model\PeopleSearch;


class ProfileElasticaRepository extends Repository
{

    protected function keywordFieldSearch($searchPhrase)
    {
        $query = new Query\MultiMatch();
        $query->setQuery($searchPhrase);
        $query->setType('best_fields');
        $query->setFields([
            'subtitle.autocomplete^4',
            'subtitle2.autocomplete^2',
        ]);

        $query->setTieBreaker(0.3);
        $query->setFuzziness(1);
        $query->setMinimumShouldMatch('100%');

        return $query;
    }

    public function generalSearch($searchPhrase)
    {
        $query = $this->keyWordFieldSearch($searchPhrase);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Query\Terms('isPublished', [1])
        );

        $boolQuery->addMust([$query]);
        $query = new Query($boolQuery);


        return $query;
    }

    public function getRegisteredUsers()
    {
        $matchAll = new Query\MatchAll();
        $query = new Query($matchAll);
        $query->setSize(0);
        return $query;
    }

    public function getPublishedProfiles($count = true)
    {
        $queryMatch = new Query\Match();
        $queryMatch->setField('isPublished', 1);
        $query = new Query($queryMatch);
        if ($count) {
            $query->setSize(0);
        }
        return $query;
    }

    public function getUserByRole($role, $online = 0, $size = 0)
    {
        $queryMatch = new Query\Match();
        $queryMatch->setField('user.roles', $role);
        $boolQuery = new Query\BoolQuery();

        if ($online) {
            $boolQuery->addMust(
                new Query\Terms('user.online', [$online])
            );
            $boolQuery->addMust($queryMatch);
            $query = new Query($boolQuery);
        } else {
            $query = new Query($queryMatch);
        }

        $query->setSize($size);
        return $query;
    }


    public function getUserByEmail($email)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('user.email.raw', [$email]));
        $query = new Query($boolQuery);
        $query->setSize(1);

        return $query;
    }


    /**
     * @return Query
     */
    public function getCategoriesAggregationQuery()
    {
        $boolQuery = new Query\BoolQuery();

        // we want only published profiles
        $boolQuery->addMust(
            new Query\Terms('isPublished', [1])
        );

        $query = new Query($boolQuery);
        $query->addAggregation($this->getCategoriesAggregation());

        // we don't need the search results, only statistics
        $query->setSize(0);

        return $query;
    }

    public function adminPeopleSearch(AdminPeopleSearch $adminPeopleSearch)
    {
        if ($adminPeopleSearch->getInput()) {
            $queryMatch = new Query\MultiMatch();
            $queryMatch->setQuery($adminPeopleSearch->getInput());
            $queryMatch->setType('best_fields');
            $queryMatch->setFields(
                [
                    'user.email.ngram^8',
                    'subtitle.ngram^4',
                ]
            );
            $queryMatch->setOperator('and');
            $queryMatch->setTieBreaker(0.3);
            $queryMatch->setMinimumShouldMatch('100%');
            $query = new Query($queryMatch);
        } elseif ($adminPeopleSearch->getUser()) {
            $query = $this->matchQuery('subtitle.ngram', $adminPeopleSearch->getUser());
        } elseif ($adminPeopleSearch->getUserEmail()) {
            $query = $this->matchQuery('user.email.ngram', $adminPeopleSearch->getUserEmail());

        } elseif ($adminPeopleSearch->getProfileRegistration()) {
            $boolQuery = new Query\BoolQuery();
            $dateString = $adminPeopleSearch->getProfileRegistration();

            $boolQuery->addMust(
                new Query\Range('createdAt',
                    [
                        'gte' => $dateString,
                        'lte' => $dateString,
                        'format' => 'dd/MM/yyyy',
                        //@todo must check os and php time_zone both of them must match (utc)
                        'time_zone' => '+02:00'
                    ]
                )
            );

            $query = new Query($boolQuery);
        } elseif ($adminPeopleSearch->getUserLastLogin()) {
            $boolQuery = new Query\BoolQuery();
            $dateString = $adminPeopleSearch->getUserLastLogin();
            $boolQuery->addMust(
                new Query\Range('user.lastLogin',
                    [
                        'gte' => $dateString,
                        'lte' => $dateString,
                        'format' => 'dd/MM/yyyy',
                        //@todo must check os and php time_zone both of them must match (utc)
                        'time_zone' => '+02:00'
                    ]
                )
            );
            $query = new Query($boolQuery);
        } else {
            $queryMatchAll = new Query\MatchAll();
            $query = new Query($queryMatchAll);
        }

        if ($adminPeopleSearch->getOrder() && $adminPeopleSearch->getOrderCol()) {
            $column = null;

            switch ($adminPeopleSearch->getOrderCol()) {
                case 'user':
                    $column = 'subtitle.raw';
                    break;
                case 'email':
                    $column = 'user.email.raw';
                    break;
                case 'registration':
                    $column = 'createdAt';
                    break;
                case 'lastLogin':
                    $column = 'user.lastLogin';
                    break;
                case 'role':
                    $column = 'user.roles';
                    break;

            }

            if ($column) {
                $sortArray = [
                    $column =>
                        [
                            'order' => $adminPeopleSearch->getOrder(),
                            'missing' => PHP_INT_MAX - 1
                        ]
                ];

                $query->addSort($sortArray);
            }
        }

        return $query;

    }

    /**
     * @param PeopleSearch $peopleSearch
     * @param array $subcategories
     * @return Query|Query\MatchAll|Query\MultiMatch
     */
    public function search(PeopleSearch $peopleSearch, $subcategories)
    {

        // we create a multi match query to return all the profiles when a search phrase is used
        if ($peopleSearch->getSearchPhrase() != null && $peopleSearch != '') {
            $query = $this->keyWordFieldSearch($peopleSearch->getSearchPhrase());
        } else {
            // but if the criteria search phrase isn't specified, we use a normal query to find all matches
            $query = new Query\MatchAll();
        }

        $boolQuery = new Query\BoolQuery();

        /*
            Category filter
            We add this filter only when the category is not null
        */

        if (null !== $peopleSearch->getCategory()) {
            $boolQuery->addMust(
                new Query\Terms('oldCategories.parent.slug', [$peopleSearch->getCategory()->getSlug()])
            );
        }


        if (!empty($peopleSearch->getUserFavourites())) {
            $boolQuery->addMust(
                new Query\Terms('id', $peopleSearch->getUserFavourites())
            );
        }


        // check if the search has filtered multiple subcategories
        if ($peopleSearch->getSubcategories()) {
            $boolQuery->addMust(
                new Query\Terms('oldCategories.id', $peopleSearch->getSubcategories())
            );
        }

        // add geo location search
        if ($peopleSearch->getLocation()) {
            $boolQuery->addMust(
                new Query\GeoDistance('contactSection.geolocation', $peopleSearch->getLocation(), $peopleSearch->getArea())
            );
        }

        if (!$peopleSearch->isFavorite()) {
            // Published or not filter
            // $peopleSearch->isPublished() may also be null
            $boolQuery->addFilter(
                new Query\Terms('isPublished', [(int)$peopleSearch->isPublished()])
            );
        }

        $boolQueryOrganization = new Query\BoolQuery();

        if (null !== $peopleSearch->getOrganization()) {
            $slug = [$peopleSearch->getOrganization()];
            $boolQueryOrganization->addShould(new Query\Terms('experience.organization.slug', $slug));
            $boolQueryOrganization->addShould(new Query\Terms('productionParticipations.production.organizationRelated.slug', $slug));
            $boolQueryOrganization->addShould(new Query\Terms('qualificationSection.qualifications.organizationRelated.slug', $slug));
        }

        $boolQuery->addMust([$boolQueryOrganization, $query]);

        $query = new Query($boolQuery);

        // we add an aggregation to our subcategories when a main category is chosen
        if (null !== $peopleSearch->getCategory() && !empty($subcategories)) {
            $query->addAggregation($this->getSubcategoriesAggregation($subcategories));
        } else {
            $query->addAggregation($this->getCategoriesAggregation());
        }

        $query->setHighlight([
            'pre_tags' => ['<em class="search-text">'],
            'post_tags' => ['</em>'],
            'order' => 'score',
            'fields' => [
                'subtitle*.autocomplete' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
            ]
        ]);

        return $query;
    }

    private function getCategoriesAggregation()
    {
        // Simple aggregation (based on categories, we get the doc_count for each parent category)
        $categoriesAggregation = new Aggregation\Terms('categories');
        $categoriesAggregation->setField('oldCategories.parent.slug');
        $categoriesAggregation->setSize(11);
        $categoriesAggregation->setExclude('categories-.*');
        return $categoriesAggregation;
    }

    private function getSubcategoriesAggregation($subcategories)
    {

        // Simple aggregation (based on subcategories, we get the doc_count for each subcategory)
        $subcategoriesAggregation = new Aggregation\Filters('subcategories');
        foreach ($subcategories as $key => $value) {
            $term = new Filter\Term();
            $term->setTerm('oldCategories.id', $key);
            $subcategoriesAggregation->addFilter($term);
        }
        return $subcategoriesAggregation;
    }


    /**
     * Get random profiles
     * @return Query
     */
    public function randomProfiles()
    {
        // Match published profile
        $queryMatch = new Query\MatchAll();
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($queryMatch);
        // Profile must be published
        $boolQuery->addMust(new Query\Terms('isPublished', [1]));
        // Media image should be profile photo
        $boolQuery->addMust(new Query\Terms('mediaImage.isProfilePhoto', [1]));
        // Random
        $seed = time() . rand(10000, 20000);
        $randomScore = new Query\FunctionScore();
        $randomScore->setRandomScore($seed);
        $boolQuery->addShould($randomScore);

        // Add media images aggregation
        $query = new Query($boolQuery);
        $mediaAggregation = new Aggregation\Terms('mediaImage');
        $mediaAggregation->setField('mediaImage.id');
        $query->addAggregation($mediaAggregation);

        return $query;
    }

    /**
     * @param  string $fieldName
     * @param $value
     * @return Query
     */
    protected function matchQuery($fieldName, $value)
    {
        $queryMatch = new Query\Match();
        $queryMatch->setFieldQuery($fieldName, $value);
        $queryMatch->setFieldOperator($fieldName, 'and');
        $queryMatch->setFieldMinimumShouldMatch($fieldName, '100%');
        return new Query($queryMatch);
    }
}