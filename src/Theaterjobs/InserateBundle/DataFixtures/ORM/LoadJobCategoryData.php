<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;

/**
 * Datafixtures for the Jobcategories.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadJobCategoryData extends CategoryData {

    protected $rootname = "categories of jobs";
    protected $rootnameDE = "Jobkategorien";
    protected $refname = "jobcategory";

    /**
     * @return number
     */
    public function getOrder() {
        return 2;
    }

    /**
     * Get an array with categories.
     *
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        $categories = array(
            'Schauspiel' => array(
                'Autoren',
                'Schauspieler (männliche Rollen)',
                'Schauspieler (weibliche Rollen)',
                'Schauspielregie',
                'Schauspielmusik',
                'Sonstige Schauspiel',
            ),
            'Tanz' => array(
                'Choreografie / Direktion',
                'Tänzer (männliche Rollen)',
                'Tänzer (weibliche Rollen)',
                'sonstige Tanz',
            ),
            'Musiktheater' => array(
                'Komposition',
                'Dirigat, Musikalische Leitung',
                'Korrepetition',
                'Musiktheaterregie',
                'Sänger (männliche Solopartien)',
                'Sänger (weibliche Solopartien)',
                'sonstige Musiktheater',
            ),
            'Chor' => array(
                'Chorleitung',
                'Sopran',
                'Mezzo',
                'Alt',
                'Tenor',
                'Bariton',
                'Bass',
                'sonstige Chor',
            ),
            'Orchester' => array(
                'Streichinstrumente',
                'Blechblasinstrumente',
                'Holzblasinstrumente',
                'sonstige Orchester',
            ),
            'Ausstattung' => array(
                'Ausstattung',
                'Bühnenbild',
                'Bühnenmalerei und -plastik',
                'Dekoration',
                'Requisite',
                'Schlosserei',
                'Tischlerei',
                'Kostümbild',
                'Ankleidedienst',
                'Gewandmeisterei',
                'Schneiderei',
                'Maske',
                'Lichtdesign',
                'Sounddesign',
                'sonstige Ausstattung',
            ),
            'Technik' => array(
                'Inspizienz',
                'Beleuchtungstechnik',
                'Ton- und Videotechnik',
                'Technische Leitung',
                'Techn. Produktionsleitung',
                'Werkstättenleitung',
                'Bühneninspektion',
                'Bühnen- und Veranstaltungstechnik',
                'Haustechnik',
                'sonstige Technik',
            ),
            'Organisation' => array(
                'Dramaturgie',
                'Intendanz',
                'Intendanzsekretariat',
                'Soufflage',
                'Theaterpädagogik',
                'Disposition (KBB)',
                'Presse- und Öffentlichkeitsarbeit',
                'Produktionsleitung / Company Management',
                'Web / Grafikdesign',
                'Akquise / Booking',
                'Marketing / Werbung',
                'Sponsoring / Fundraising',
                'sonstige Organisation',
            ),
            'Administration' => array(
                'Verwaltungsleitung / kaufm. GF',
                'Personalwesen',
                'Kasse',
                'Finanz- und Rechnungswesen',
                'Vertrieb',
                'Archiv / Bibliothek',
                'Hausverwaltung',
                'IT / EDV',
                'Service / Vorderhaus',
                'sonstige Administration',
            ),
            'Sonstige' => array(
                'Performance',
                'Moderation / Sprechen',
                'Entertainment',
                'Artistik / Kleinkunst',
                'Puppenspiel / Figurentheater',
            ),
            'Bildung' => array(
                'Bildung Schauspiel',
                'Bildung Musiktheater',
                'Bildung Tanz',
                'Bildung Chor',
                'Bildung Orchester',
                'Bildung Ausstattung',
                'Bildung Technik',
                'Bildung Organisation',
                'Bildung Administration',
                'Sonstige Bildung',
            )
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
