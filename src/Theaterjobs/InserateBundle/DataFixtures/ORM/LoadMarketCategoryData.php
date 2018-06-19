<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;

/**
 * Datafixtures for the Marketcategories.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadMarketCategoryData extends CategoryData {

    protected $rootname = "categories of markets";
    protected $rootnameDE = "Marktkategorien";
    protected $refname = "marketcategory";

    /**
     * @return int
     */
    public function getOrder() {
        return 20; // the order in which fixtures will be loaded
    }

    /**
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        $categories = array(
            "Stücke und Programme" => array(
                "Stücke",
                "Werke",
                "Sprechtheater",
                "Musiktheater",
                "Tanztheater",
                "Konzert",
                "Kurzprogramme bis 30 min.",
                "andere Stücke und Programme",
                "Kindertheater",
            ),
            "Klein und Kunst" => array(
                "Kabarett",
                "Musikshow",
                "A Cappella",
                "Comedy",
                "Magie, Zauberei",
                "Pantomime",
                "Artistik / Akrobatik",
                "Walking Acts",
                "Theatersport",
                "Variete",
                "andere Klein aber Kunst",
            ),
            "Lernen & Bildung" => array(
                "Schauspielunterricht",
                "Gesangsunterricht",
                "Tanzunterricht",
                "Sprechunterricht",
                "Improunterricht",
                "Kampfunterricht",
                "Entspannungsunterricht",
                "Coaching",
                "Korrepetitionsunterricht",
                "Workshops / Seminare",
                "anderer Unterricht",
                "Berufsausbildung",
                "berufl. Weiterbildung",
                "geförderte Jobs",
                "unvergütete Hospitanzen/Praktika",
            ),
            "Bühne & Technik" => array(
                "Bühnenbau",
                "Bühnentechnik",
                "Lichttechnik",
                "Tontechnik",
                "SFX, Pyro",
                "andere Bühne & Technik",
            ),
            "Wohn & Raum" => array(
                "Wohnen auf Zeit",
                "Mit-Wohnen",
                "Proberäume",
                "Ateliers",
                "Aufführungsräume",
                "anderer Wohn & Raum",
            ),
            "Präsentation" => array(
                "Internet",
                "Printmedien",
                "Video",
                "Fotografie",
                "Werbung",
                "andere Präsentation",
                "Audio",
            ),
            "Rat & Vermittlung" => array(
                "Finanzen",
                "Stiftungen",
                "Steuern",
                "Recht",
                "Agenturen",
                "Casting",
                "Kulturmanagement",
                "anderer Rat & Vermittlung",
            ),
            "Bühne & Kunst" => array(
                "Kostümschneiderei",
                "Maskenbau",
                "Perücken und Makeup",
                "Requisitenbau",
                "Puppenbau",
                "Instrumentenbau",
                "Organization",
                "andere Bühne & Kunst",
            ),
            "Kontakte & Netzwerk" => array(
                "Festivals",
                "Mitstreiter gesucht",
                "Gastspielmöglichkeiten",
                "Wettbewerbe mit Teilnahmegebühr",
                "Hochschulprojekte",
                "Franchise"
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
