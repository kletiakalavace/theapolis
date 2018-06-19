<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\MarketCategoryMapping;

/**
 * Datafixtures for the MarketCategoryMapping.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Heiko Jurgeleit <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadMarketCategoryMappingData extends AbstractFixture implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {

        $catRefs = array(
            "marketcategory_categories of markets_Stücke und Programme_Stücke" => "new_marketcategory_new categories of markets_Gastspiele_andere Gastspiele",
            "marketcategory_categories of markets_Stücke und Programme_Werke" => "new_marketcategory_new categories of markets_Gastspiele_andere Gastspiele",
            "marketcategory_categories of markets_Stücke und Programme_Sprechtheater" => "new_marketcategory_new categories of markets_Gastspiele_Sprechtheater",
            "marketcategory_categories of markets_Stücke und Programme_Musiktheater" => "new_marketcategory_new categories of markets_Gastspiele_Musiktheater",
            "marketcategory_categories of markets_Stücke und Programme_Tanztheater" => "new_marketcategory_new categories of markets_Gastspiele_Tanztheater",
            "marketcategory_categories of markets_Stücke und Programme_Kindertheater" => "new_marketcategory_new categories of markets_Gastspiele_Kinder- und Jugendtheater",
            "marketcategory_categories of markets_Stücke und Programme_Konzert" => "new_marketcategory_new categories of markets_Gastspiele_Konzert",
            "marketcategory_categories of markets_Stücke und Programme_Kurzprogramme bis 30 min." => "new_marketcategory_new categories of markets_Gastspiele_Kurzprogramme bis 30 min.",
            "marketcategory_categories of markets_Stücke und Programme_andere Stücke und Programme" => "new_marketcategory_new categories of markets_Gastspiele_andere Gastspiele",
            "marketcategory_categories of markets_Klein und Kunst_Kabarett" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Musikshow" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_A Cappella" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Comedy" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Magie, Zauberei" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Walking Acts" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Theatersport" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_Variete" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Klein und Kunst_andere Klein aber Kunst" => "new_marketcategory_new categories of markets_Gastspiele_Kleinkunst",
            "marketcategory_categories of markets_Lernen & Bildung_Schauspielunterricht" => "new_marketcategory_new categories of markets_Bildung_Schauspiel",
            "marketcategory_categories of markets_Lernen & Bildung_Gesangsunterricht" => "new_marketcategory_new categories of markets_Bildung_Stimme und Gesang",
            "marketcategory_categories of markets_Lernen & Bildung_Tanzunterricht" => "new_marketcategory_new categories of markets_Bildung_Tanz, Bewegung, Körper",
            "marketcategory_categories of markets_Lernen & Bildung_Sprechunterricht" => "new_marketcategory_new categories of markets_Bildung_Stimme und Gesang",
            "marketcategory_categories of markets_Lernen & Bildung_Improunterricht" => "new_marketcategory_new categories of markets_Bildung_Tanz, Bewegung, Körper",
            "marketcategory_categories of markets_Lernen & Bildung_Kampfunterricht" => "new_marketcategory_new categories of markets_Bildung_Tanz, Bewegung, Körper",
            "marketcategory_categories of markets_Lernen & Bildung_Entspannungsunterricht" => "new_marketcategory_new categories of markets_Bildung_Tanz, Bewegung, Körper",
            "marketcategory_categories of markets_Lernen & Bildung_Coaching" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Lernen & Bildung_Korrepetitionsunterricht" => "new_marketcategory_new categories of markets_Bildung_Stimme und Gesang",
            "marketcategory_categories of markets_Lernen & Bildung_Workshops / Seminare" => "new_marketcategory_new categories of markets_Bildung_andere Bildung",
            "marketcategory_categories of markets_Lernen & Bildung_anderer Unterricht" => "new_marketcategory_new categories of markets_Bildung_andere Bildung",
            "marketcategory_categories of markets_Bühne & Technik_Bühnenbau" => "new_marketcategory_new categories of markets_Netzwerk_Technik, Ton, Licht",
            "marketcategory_categories of markets_Bühne & Technik_Bühnentechnik" => "new_marketcategory_new categories of markets_Netzwerk_Technik, Ton, Licht",
            "marketcategory_categories of markets_Bühne & Technik_Lichttechnik" => "new_marketcategory_new categories of markets_Netzwerk_Technik, Ton, Licht",
            "marketcategory_categories of markets_Bühne & Technik_Tontechnik" => "new_marketcategory_new categories of markets_Netzwerk_Technik, Ton, Licht",
            "marketcategory_categories of markets_Bühne & Technik_andere Bühne & Technik" => "new_marketcategory_new categories of markets_Netzwerk_Technik, Ton, Licht",
            "marketcategory_categories of markets_Wohn & Raum_Wohnen auf Zeit" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Wohn & Raum_Mit-Wohnen" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Wohn & Raum_Proberäume" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Wohn & Raum_Ateliers" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Wohn & Raum_Aufführungsräume" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Wohn & Raum_anderer Wohn & Raum" => "new_marketcategory_new categories of markets_Netzwerk_Aufführungs- und Probenräume",
            "marketcategory_categories of markets_Präsentation_Internet" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_Printmedien" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_Audio" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_Video" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_Fotografie" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_Werbung" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Präsentation_andere Präsentation" => "new_marketcategory_new categories of markets_Netzwerk_Präsentation",
            "marketcategory_categories of markets_Rat & Vermittlung_Finanzen" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_Stiftungen" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_Recht" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_Agenturen" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_Casting" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_Kulturmanagement" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Rat & Vermittlung_anderer Rat & Vermittlung" => "new_marketcategory_new categories of markets_Netzwerk_Rat und Vermittlung",
            "marketcategory_categories of markets_Bühne & Kunst_Kostümschneiderei" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_Maskenbau" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_Perücken und Makeup" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_Requisitenbau" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_Puppenbau" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_Organization" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Bühne & Kunst_andere Bühne & Kunst" => "new_marketcategory_new categories of markets_Netzwerk_Maske, Kostüm, Bühne",
            "marketcategory_categories of markets_Kontakte & Netzwerk_Mitstreiter gesucht" => "new_marketcategory_new categories of markets_Netzwerk_Mitstreiter gesucht",
            "marketcategory_categories of markets_Kontakte & Netzwerk_Hochschulprojekte" => "new_marketcategory_new categories of markets_Netzwerk_Spielmöglichkeiten",
            "marketcategory_categories of markets_Kontakte & Netzwerk_Festivals" => "new_marketcategory_new categories of markets_Netzwerk_Spielmöglichkeiten",
            "marketcategory_categories of markets_Kontakte & Netzwerk_Gastspielmöglichkeiten" => "new_marketcategory_new categories of markets_Netzwerk_Spielmöglichkeiten",
            "marketcategory_categories of markets_Kontakte & Netzwerk_Franchise" => "new_marketcategory_new categories of markets_Netzwerk_Mitstreiter gesucht",
        );

        foreach ($catRefs as $old => $new) {
            $mapping = new MarketCategoryMapping();
            $mapping->setOldMarket($this->getReference($old));
            $mapping->setNewMarket($this->getReference($new));
            $manager->persist($mapping);
        }

        $manager->flush();
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 50;
    }

}
