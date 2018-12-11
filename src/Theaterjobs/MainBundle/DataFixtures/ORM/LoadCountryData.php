<?php

namespace Theaterjobs\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\MainBundle\Entity\Country;
use Theaterjobs\MainBundle\Entity\CountryTranslation;

/**
 * Datafixtures for the Countries.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadCountryData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
//        $translatable = $this->container->get('gedmo.listener.translatable');
//        $translatable->setTranslatableLocale('en');
//
//        $kernel = $this->container->get('kernel');
//        $path = $kernel->locateResource(
//            '@TheaterjobsMainBundle/DataFixtures/SQL/tm_land_tree.csv'
//        );
//
//        $shortSelected = array('Switzerland', 'Austria', 'Germany');
//
//        $refs = array(); // We will persist them later
//        $unrefed = array(); // Cypres made problems. We recall them to set the parents, that aren't created in the foreach yet
//        foreach ($this->csvToArray($path) as $land) {
//            $country = new Country();
//            $country->setName($land['int_name']);
//            if (in_array($land['int_name'], $shortSelected)) {
//                $country->setIsShortSelect(true);
//            }
//            $country->addTranslation(new CountryTranslation('de', 'name', $land['name']));
//
//            if ($this->hasReference("country_{$land['tree_parent']}")) {
//                $country->setParent($this->getReference("country_{$land['tree_parent']}"));
//            } elseif($land['tree_parent'] != 'NULL') { // parent may not been created yet
//                array_push($unrefed, array($land['id'], $land['tree_parent']));
//            }
//
//            array_push($refs, $country);
//            $this->setReference("country_{$land['id']}", $country);
//        }
//
//        // Now persist them
//        foreach( $refs as $ref) {
//            $manager->persist($ref);
//        }
//
//        $manager->flush();
//
//        // Recall the unrelated parents
//        foreach ($unrefed as $unref) {
//            if ($this->hasReference("country_{$unref[0]}") && $this->hasReference("country_{$unref[1]}")) {
//                $this->getReference("country_{$unref[0]}")->setParent($this->getReference("country_{$unref[1]}"));
//                $manager->persist($this->getReference("country_{$unref[0]}"));
//                $manager->flush();
//            }
//        }
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 10;
    }

    /**
     * Reads data from csv and returns an php array.
     *
     * The first line of the csv hast to contain the fieldnames.
     *
     * @param string $filename
     * @param string $delimiter
     * @return boolean|multitype:multitype:
     */
    private function csvToArray($path = '', $delimiter = ';') {
        if (!file_exists($path) || !is_readable($path))
            die($path . " not there");


        $data = array();
        if (($handle = fopen($path, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, $delimiter);

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

}
