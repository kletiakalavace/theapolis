<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\MainBundle\Entity\Address;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadOrganizationData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
     * REMEMBER TO REPLACE "Andere" with "andere" in the cvs!
     *
     * For the Logos to move there is a command 'theaterjobs:move-organization-logos'.
     *
     * @TODO what about the fields 'show_in_list' and 'show_in_register' ?
     */
    public function load(ObjectManager $manager) {
        $kernel = $this->container->get('kernel');
        $path = $kernel->locateResource(
                '@TheaterjobsInserateBundle/DataFixtures/SQL/partner.csv'
        );

        $orgas = $this->csvToArray($path);
        $console = new ConsoleOutput();
        $console->writeln("Ok,will take some time to load " . count($orgas) . " organizations");

        $exitOn = 1000;
        $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
        $console->getFormatter()->setStyle('fire', $style);
        $console->writeln("<fire>FOR NOW WE ONLY USE $exitOn organizations.");
        $console->writeln("SO FIX THIS IF YOU NEED MORE!!!</fire>");
        $console->writeln("...please be patient!");

        $i = 0;

        foreach ($orgas as $orga) {
            if ($exitOn == $i)
                break;
            $organization = new Organization();
            $organization->setName($orga['institution']);

            if ($orga['logo'] != "NULL")
                $organization->setPath($orga['logo']);

            $organization->setIsVisibleInList((bool) $orga['show_in_list']);
            $organization->setIsVisibleInRegister((bool) $orga['show_in_register']);

            /*
              NULL
              e.V.
              gGmbH
              bitte auswählen
              GmbH
              Körperschaft d. öffentl. Rechts => 'KdöR'
              Andere
              Einzelunternehmen
              GbR
              Stiftung
              AG
              UG
              KG
              OHG
             */

            if ($orga['unternehmensform'] == 'NULL' OR
                    $orga['unternehmensform'] == 'bitte auswählen' OR
                    $orga['unternehmensform'] == 'Andere') {
                $orga['unternehmensform'] = 'andere';
            } else if ($orga['unternehmensform'] == 'Körperschaft d. öffentl. Rechts') {
                $orga['unternehmensform'] = 'KdöR';
            }

            if ($this->hasReference("orga_" . $orga['unternehmensform'])) {
                $organization->setForm($this->getReference("orga_" . $orga['unternehmensform']));
            }

//            if ($this->hasReference("orga_" . $orga['unternehmensform'])) {
//                $organization->setForm($this->getReference("orga_" . $orga['unternehmensform']));
//            } else {
//                $console->writeln("No ref to " . "orga_" . $orga['unternehmensform']);
//            }

            $isAddr = false;

            // @TODO Do we use this in current System JANA
            $address = new Address();
            if ($orga['strasse'] != 'NULL') {
                $address->setStreet($orga['strasse']);
                $isAddr = true;
            }
            if ($orga['plz'] != 'NULL') {
                $address->setZip($orga['plz']);
                $isAddr = true;
            }
            if ($orga['ort'] != 'NULL') {
                $address->setCity($orga['ort']);
                $isAddr = true;
            }
            if ($orga['tel'] != 'NULL') {
                $address->setPhone($orga['tel']);
                $isAddr = true;
            }
            if ($orga['fax'] != 'NULL') {
                $address->setFax($orga['fax']);
                $isAddr = true;
            }
            if ($orga['url'] != 'NULL') {
                $address->setUrl($orga['url']);
                $isAddr = true;
            }
            if ($orga['email'] != 'NULL') {
                $address->setEmail($orga['email']);
                $isAddr = true;
            }

//            if ($this->hasReference("country_" . $orga['landtree_id'])) {
//                $address->setCountry($this->getReference("country_" . $orga['landtree_id']));
//            }
            $address->setCountry('DE');

            if ($isAddr) {
                $organization->setAddress($address);
            }

            $manager->persist($organization);

            $this->setReference("organization_{$orga['institution']}", $organization);
            $i++;
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
        return 60;
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
