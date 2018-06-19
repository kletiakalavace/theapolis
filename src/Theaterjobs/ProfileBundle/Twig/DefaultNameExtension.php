<?php


namespace Theaterjobs\ProfileBundle\Twig;

use Symfony\Component\Security\Acl\Exception\Exception;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Get default name artistName/realName of user
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class DefaultNameExtension extends \Twig_Extension
{
    /**
     * Returns name of filter
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('defaultName', array($this, 'defaultNameFilter')),
        );
    }

    /**
     * Get the selected profile name of the user
     *
     * @param array|User|Profile $profile profile entity
     *
     * @return String
     */
    public function defaultNameFilter($profile)
    {
        try {
            //Check if $profile is type of Profile Entity
            if ($profile instanceOf Profile) {
                return $profile->defaultName();

            //Check if $profile is Type of User Entity
            } else if ($profile instanceof User) {
                return $profile->getProfile()->defaultName();

            //Check if $profile is array, Comes from Elastic Search
            } else if (is_array($profile)) {
                return $this->getArrayProfileName($profile);
            } else {
                throw new Exception('Invalid $profile type');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return defualt profile name
     *
     * @param Array $profile Profile array from elasticsearch
     *
     * @return String profile name
     */
    public function getArrayProfileName($profile)
    {
        try {
            if ((isset($profile['profileName']) && isset($profile['subtitle'])) && ($profile['subtitle'] && $profile['profileName'])) {
                return $profile['subtitle'];
            } else if (isset($profile['firstName']) && isset($profile['lastName'])) {
                return $profile['firstName'] . ' ' . $profile['lastName'];
            } else {
                // lets help the poor developer not to guess what fields are missing :)
                throw new Exception('Missing fields in Elastica Results [ profileName, subtitle, firstName, lastName ]');

            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Name of extension
     *
     * @return String
     */
    public function getName()
    {
        return 'defaultName_extension';
    }
}
