<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;

/**
 * Datafixtures for the pitch of voices.
 *
 * @category DataFixtures
 * @package  Theaterjobs\ProfileBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadVoiceCategoryData extends CategoryData {

    protected $rootname = "categories of voices";
    protected $rootnameDE = "Stimmlagenkategorien";
    protected $refname = "voicecategory";

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 40;
    }

    /**
     * Get an array with categories.
     *
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        $categories = array(
            'Sopran' => array(
                'Sopran (allgemein)',
                'Dramatischer Koloratursopran',
                'Charakter-Sopran',
                'Jugendlich-dramatischer Sopran',
                'Hochdramatischer Sopran',
                'Lyrischer Sopran',
                'Lyrischer Koloratursopran',
                'Broadway-Sopran',
                'Koloratur-Soubrette',
                'Deutsche Soubrette',
                'Pop-Sopran',
                'Hohe Chansonstimme'
            ),
            'Mezzo' => array(
                'Mezzosopran (allgemein)',
                'Dramatischer Mezzosopran',
                'Koloratur-Mezzosopran',
                'Lyrischer Mezzosopran',
                'Broadway Mezzosopran'
            ),
            'Alt' => array(
                'Alt (allgemein)',
                'Dramatischer Alt',
                'Koloraturalt',
                'Lyrischer Alt',
                'Spielalt',
                'Broadway-Alt',
                'Tiefe Chansonstimme'
            ),
            'Tenor' => array(
                'Tenor (allgemein)',
                'Heldentenor',
                'Jugendlicher Heldentenor',
                'Charaktertenor',
                'Lyrischer Tenor',
                'Italienischer Tenor',
                'Tenorbuffo',
                'Broadway-Tenor',
                'Pop-Tenor',
                'Countertenor',
                'Counter-Sopran',
                'Counter-Alt',
                'Haute-Contre'
            ),
            'Bariton' => array(
                'Bariton (allgemein)',
                'Heldenbariton',
                'Charakterbariton',
                'Kavalierbariton',
                'Lyrischer Bariton',
                'Italienischer Bariton',
                'Spielbariton',
                'Broadway-Bariton'
            ),
            'Bass' => array(
                'Bass (allgemein)',
                'Seriöser Bass',
                'Charakterbass',
                'Bassbariton',
                'Bassbuffo'
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
