<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\InserateBundle\Entity\Tags;
use Carbon\Carbon;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Datafixtures for the Directors.
 *
 * @category DataFixtures
 * @package  Theaterjobs\ProfileBundle\DataFixtures\ORM
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadTagsStageData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 125;
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
    private function csvToArray($path = '', $delimiter = ',') {
        if (!file_exists($path) || !is_readable($path))
            die($path . " not there");


        $data = array();
        if (($handle = fopen($path, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, $delimiter);

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    public function load(ObjectManager $manager) {

        $kernel = $this->container->get('kernel');
        $path = $kernel->locateResource(
                '@TheaterjobsInserateBundle/DataFixtures/SQL/tags_stage.csv'
        );
        $tags = $this->csvToArray($path);

        $admin = $manager->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(['id' => '3']);

        foreach ($tags as $value) {
            $tag = new Tags();
            $tag->setTitle($value[0]);
            $tag->setCheckedAt(Carbon::now());
            $tag->setCheckedBy($admin);
            $tag->setChecked(true);
            
            //$console->writeln("Creator $value[0] saved.");

            $manager->persist($tag);
        }
        $manager->flush();
    }

}
