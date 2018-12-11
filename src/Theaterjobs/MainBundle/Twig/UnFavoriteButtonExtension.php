<?php

namespace Theaterjobs\MainBundle\Twig;

use Elastica\Result;
use Theaterjobs\UserBundle\Entity\User;

/**
 * UnFavoriteExtension ElasticSearch Results
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class UnFavoriteButtonExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('un_favorite', [$this, 'unFavoriteButtonFilter']),
        ];
    }

    /**
     * @param Result $result
     * @param User $user
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    public function unFavoriteButtonFilter(Result $result, User $user, $type)
    {
        if (!$user) {
            return false;
        }

        switch ($type) {
            case "people":
                $favorites = $user->getProfile()->getUserFavourite();
                break;
            case "job":
                $favorites = $user->getProfile()->getJobFavourite();
                break;
            case "news":
                $favorites = $user->getProfile()->getNewsFavourite();
                break;
            case "organization":
                $favorites = $user->getProfile()->getOrganisationFavourite();
                break;
            default:
                throw new \Exception('Unsupported type!');
        }


        return $favorites->exists(function ($key, $element) use ($result) {
            return $result->getId() == $element->getId();
        });
    }
}