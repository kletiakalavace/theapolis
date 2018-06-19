<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;

/**
 * Datafixtures for the Marketcategories.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadNewMarketCategoryData extends CategoryData {

    protected $rootname = "new categories of markets";
    protected $rootnameDE = "neue Marktkategorien";
    protected $refname = "new_marketcategory";

    /**
     * @return int
     */
    public function getOrder() {
        return 30; // the order in which fixtures will be loaded
    }

    /**
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        $categories = array(
            'Gastspiele' => array(
                'Sprechtheater',
                'Musiktheater',
                'Tanztheater',
                'Kinder- und Jugendtheater',
                'Konzert',
                'Kurzprogramme bis 30 min.',
                'Kleinkunst',
                'Spielmöglichkeiten',
                'andere Gastspiele'
            ),
            'Bildung' => array(
                'Schauspiel',
                'Stimme und Gesang',
                'Tanz, Bewegung, Körper',
                'andere Bildung'           
            ),
            'Netzwerk' => array(
                'Aufführungs- und Probenräume',
                'Rat und Vermittlung',
                'Präsentation',
                'Spielmöglichkeiten',
                'Technik, Ton, Licht',
                'Maske, Kostüm, Bühne',
                'Mitstreiter gesucht',
                'andere Netzwerk'
            ),
        );

        return $categories;
    }

    public function getRefName() {
        return $this->refname;
    }

    public function getRootName() {
        return $this->rootname;
    }

    public function getRootNameDE() {
        return $this->rootnameDE;
    }

}
