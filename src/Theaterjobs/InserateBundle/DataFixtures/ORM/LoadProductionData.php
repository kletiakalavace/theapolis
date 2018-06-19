<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Theaterjobs\ProfileBundle\Entity\Creator;
use Theaterjobs\ProfileBundle\Entity\Director;
use Theaterjobs\ProfileBundle\Entity\Production;

/**
 * Datafixtures for the productions.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadProductionData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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


    public function load(ObjectManager $manager) {
        $kernel = $this->container->get('kernel');
        $path = $kernel->locateResource(
                '@TheaterjobsInserateBundle/DataFixtures/SQL/profile_productions.csv'
        );

        $productions = $this->csvToArray($path);
        $console = new ConsoleOutput();
        $console->writeln("Ok,will take some time to load " . count($productions) . " productions");

//        $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
//        $console->getFormatter()->setStyle('fire', $style);
//        $console->writeln("<fire>FOR NOW WE ONLY USE $exitOn organizations.");
//        $console->writeln("SO FIX THIS IF YOU NEED MORE!!!</fire>");
//        $console->writeln("...please be patient!");

        $admin = $manager->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(['id' => '3']);

        foreach ($productions as $value) {
            $production = new Production();
            $production->setName($value['title']);
            $production->setYear($value['premiere']);
            // find related orga by name
            $orga = $manager->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(['name' => $value['organization']]);
            $production->setOrganizationRelated($orga);

            // find one or more creators
            $creatorNames = explode('/',$value['creators']);
            foreach ($creatorNames as $creatorName ) {
                $creator = $manager->getRepository('TheaterjobsProfileBundle:Creator')->findOneBy(['name' => trim($creatorName)]);
                if ($creator instanceof Creator){
                    $production->addCreators($creator);
                }

            }

            // find one or more directors
            $directorNames = explode('/',$value['directors']);
            foreach ($directorNames as $directorName ) {
                $director = $manager->getRepository('TheaterjobsProfileBundle:Director')->findOneBy(['name' => trim($directorName)]);
                if ($director instanceof Director) {
                    $production->addDirectors($director);
                }

            }

            $production->setCheckedBy($admin);
            $production->setChecked(1);
            $production->setCheckedAt(new \DateTime());

            $manager->persist($production);
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
        return 960;
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
