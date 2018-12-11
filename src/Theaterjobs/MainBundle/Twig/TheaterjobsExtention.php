<?php

namespace Theaterjobs\MainBundle\Twig;

use Carbon\Carbon;

/**
 * The twig exstension
 *
 * @category Twig
 * @package  Theaterjobs\MainBundle\Twig
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class TheaterjobsExtention extends \Twig_Extension {

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     *
     * @return array
     */
    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('truncate', array($this, 'truncate')),
            new \Twig_SimpleFilter('getRole', array($this, 'getRole')),
            new \Twig_SimpleFilter('human_date', array($this, 'humanDateFilter')),
            new \Twig_SimpleFilter('slugToCategory', array($this, 'cleanUpSlugToCategory')),
        );
    }

    public function cleanUpSlugToCategory($slug) {
        return implode(array_slice(explode("-",$slug), 0, 1),"-");
    }

    public function humanDateFilter($date) {
        $date = Carbon::instance($date);
        Carbon::setLocale('de');
        return $date->diffForHumans();
    }

    /**
     * Cut a string.
     *
     * @param string  $string The string to cut.
     * @param integer $length The string lenght.
     *
     * @return string
     */
    public function truncate($string, $length) {
        if (mb_strlen(utf8_decode($string)) > $length) {
            return utf8_encode(
                    trim(mb_substr(utf8_decode($string), 0, $length))
                    . '&hellip;'
            );
        }

        return $string;
    }

    public function getRole($user) {
        $userRoles = $user->getRoles();

        return $userRoles;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     *
     * @return string
     */
    public function getName() {
        return 'theaterjobs_main_extension';
    }

}
