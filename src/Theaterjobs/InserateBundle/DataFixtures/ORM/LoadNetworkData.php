<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use \DateTime;
use Theaterjobs\InserateBundle\Entity\Network;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadNetworkData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
        return 112;
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
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function load(ObjectManager $manager) {
        $kernel = $this->container->get('kernel');
        $path = $kernel->locateResource(
                '@TheaterjobsInserateBundle/DataFixtures/SQL/network.csv'
        );
        $networks = $this->csvToArray($path);
        $console = new ConsoleOutput();

        foreach ($networks as $j) {
                
            $network = new Network();
            $network->setTitle($j['title']);
            $network->setDescription($j['description']);
            $network->setCreatedAt(new DateTime($j['createdAt']));
            $network->setPublicationEnd(new DateTime($j['publication_end']));
            $network->setUpdatedAt(new DateTime($j['updatedAt']));
            $network->setPublishedAt(new \DateTime($j['publishedAt']));
            if ($j['archivedAt'] != null)
                $network->setArchivedAt(new \DateTime($j['archivedAt']));
            //$categ = $manager->getRepository('TheaterjobsCategoryBundle:Category')->findBy(array('slug' => $j['category']));
            $qb=$manager->createQueryBuilder();
            $categ = $qb->select('child')
                            ->from('TheaterjobsCategoryBundle:Category', 'child')
                            ->innerJoin('TheaterjobsCategoryBundle:Category', 'parent')
                            ->where('parent.id = child.parent')
                            ->andWhere('parent.slug = :parent')
                            ->setParameters(array('parent' => 'netzwerk'))
                            ->getQuery()->getResult();
            $category = array_rand($categ);
            $network->addCategory($categ[$category]);
            
                if ($this->hasReference("gratification_" . $j['gratification'])) {
                    $network->setGratification($this->getReference("gratification_" . $j['gratification']));
                }

                $manager->persist($network);
                
            
        }
        $manager->flush();
    }

}
