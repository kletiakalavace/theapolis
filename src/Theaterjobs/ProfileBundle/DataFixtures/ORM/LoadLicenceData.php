<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;

/**
 * Datafixtures for driving licenses classes.
 *
 * @category DataFixtures
 * @package  Theaterjobs\ProfileBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadLicenceData extends CategoryData {

    protected $rootname = "driving license";
    protected $rootnameDE = "license";
    protected $refname = "profilelicense";

    /**
     * @return number
     */
    public function getOrder() {
        return 30;
    }

    /**
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        $categories = array(
            'Kleinkrafträder' => array('AM'),
            'Krafträder' => array('A1', 'A2', 'A'),
            'Mehrspurige Fahrzeuge' => array('B', 'BE', 'C', 'C1', 'CE','C1E'),
            'Omnibusse' => array('D', 'D1', 'DE', 'D1E'),
            'Sonstige' => array('L','T'),
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
