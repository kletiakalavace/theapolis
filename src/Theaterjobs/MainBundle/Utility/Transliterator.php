<?php

namespace Theaterjobs\MainBundle\Utility;

use Gedmo\Sluggable\Util\Urlizer;

/**
 * The Transliterator
 *
 * @category Utility
 * @package  Theaterjobs\MainBundle\Utility
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
class Transliterator {

    /**
     * Transliteration callback for slugs
     *
     * @param string $text
     * @param string $separator
     * @param object $object
     * @return callable
     */
    public static function transliterate($text, $separator, $object) {
        $text = self::my_transliteration_function($text);
        return Urlizer::urlize($text, $separator);
    }

    /**
     * Replace all mutated vowel to latin vowel.
     *
     * @param string $text
     * @return string
     */
    protected static function my_transliteration_function($text) {
        setlocale(LC_CTYPE, 'de_DE.utf8');
        return iconv("UTF-8", 'ASCII//TRANSLIT', $text);
    }

}
