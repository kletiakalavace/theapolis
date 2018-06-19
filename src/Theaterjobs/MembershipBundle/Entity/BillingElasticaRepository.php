<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 15/03/2018
 * Time: 10:29
 */

namespace Theaterjobs\MembershipBundle\Entity;


use FOS\ElasticaBundle\Repository;
use Elastica\Aggregation;
use Elastica\Query;
use Theaterjobs\AdminBundle\Model\AdminBillingSearch;
use Theaterjobs\MainBundle\Utility\ESNgramFilterTrait;


/**
 * Class BillingElasticaRepository
 * @package Theaterjobs\MembershipBundle\Entity
 */
class BillingElasticaRepository extends Repository
{
    use ESNgramFilterTrait;

    /**
     * @param int $profileId
     * @param $createdAt
     * @return Query
     */
    public function countBillingsProfile($profileId, $createdAt)
    {
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust(
            new Query\Terms('booking.profile.id', [$profileId])
        );

        // get invoices made before this one
        $boolQuery->addMust(
            new Query\Range('createdAt', [
                'lt' => $createdAt,
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ])
        );

        $query = new Query($boolQuery);

        $query->setSize(0);

        return $query;

    }


    /**
     * @param $bookingId
     * @return Query
     */
    public function countBookings($bookingId)
    {
        $boolQuery = new Query\BoolQuery();

        $boolQuery->addMust(
            new Query\Terms('booking.id', [$bookingId])
        );

        $query = new Query($boolQuery);

        $query->setSize(0);

        return $query;

    }


    /**
     * @param AdminBillingSearch $adminBillingSearch
     * @return Query
     */
    public function adminBillingSearch(AdminBillingSearch $adminBillingSearch)
    {
        if ($adminBillingSearch->getInput()) {
            $queryMatch = new Query\MultiMatch();
            $queryMatch->setQuery($adminBillingSearch->getInput());
            $queryMatch->setType('best_fields');
            $queryMatch->setFields([
                'booking.profile.full_name.ngram^12',
                'billingAddress.country.ngram^8',
                'iban.ngram^6',
                'number.ngram^4',
                'booking.paymentmethod.short.ngram^2'
            ]);
            $queryMatch->setOperator('and');
            $queryMatch->setTieBreaker(0.3);
            $queryMatch->setMinimumShouldMatch('100%');
            $query = new Query($queryMatch);
        } elseif ($adminBillingSearch->getUser()) {
            $query = $this->matchQuery('booking.profile.full_name.ngram', $adminBillingSearch->getUser());
        } elseif ($adminBillingSearch->getBillingCountry()) {
            $queryMatch = new Query\Match();
            $queryMatch->setFieldQuery('billingAddress.country.ngram', $adminBillingSearch->getBillingCountry());
            $queryMatch->setFieldOperator('billingAddress.country.ngram', 'and');
            $queryMatch->setFieldMinimumShouldMatch('billingAddress.country.ngram', '100%');
            $boolQuery = new Query\BoolQuery();
            $nestedQuery = new Query\Nested();
            $boolQuery->addMust($queryMatch);
            $nestedQuery->setQuery($boolQuery);
            $nestedQuery->setPath('billingAddress');
            $query = new Query($nestedQuery);
        } elseif ($adminBillingSearch->getBillingIban()) {
            $query = $this->matchQuery('iban.ngram', $adminBillingSearch->getBillingIban());
        } elseif ($adminBillingSearch->getBillingNr()) {
            $query = $this->matchQuery('number.ngram', $adminBillingSearch->getBillingNr());
        } elseif ($adminBillingSearch->getBillingPayment()) {
            $query = $this->matchQuery('booking.paymentmethod.short.ngram', $adminBillingSearch->getBillingPayment());
        } elseif ($adminBillingSearch->getBillingCreationFrom() && $adminBillingSearch->getBillingCreationTo()) {
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMust(
                new Query\Range('createdAt', [
                    'gte' => $adminBillingSearch->getBillingCreationFrom(),
                    'lte' => $adminBillingSearch->getBillingCreationTo(),
                    'format' => 'dd/MM/yyyy',
                    //@todo must check os and php time_zone both of them must match (utc)
                    'time_zone' => '+02:00'
                ])
            );
            $query = new Query($boolQuery);
        } else {
            $queryMatchAll = new Query\MatchAll();
            $query = new Query($queryMatchAll);
        }

        if ($adminBillingSearch->getOrder() && $adminBillingSearch->getOrderCol()) {
            $column = null;

            switch ($adminBillingSearch->getOrderCol()) {
                case 'user':
                    $column = 'booking.profile.full_name.raw';
                    break;
                case 'billingNo':
                    $column = 'number.raw';
                    break;
                case 'creation':
                    $column = 'createdAt';
                    break;
                case 'iban':
                    $column = 'iban.raw';
                    break;
                case 'country':
                    $column = 'billingAddress.country.raw';
                    break;
                case 'paymentmethod':
                    $column = 'booking.paymentmethod.short.raw';
                    break;

            }

            if ($column) {
                $sortArray = [
                    $column =>
                        [
                            'order' => $adminBillingSearch->getOrder(),
                            'missing' => PHP_INT_MAX - 1
                        ]
                ];

                $query->addSort($sortArray);
            }
        }

        return $query;
    }
}
