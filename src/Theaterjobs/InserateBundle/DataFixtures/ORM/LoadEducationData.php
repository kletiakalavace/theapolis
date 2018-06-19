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
use Theaterjobs\InserateBundle\Entity\Education;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadEducationData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
                '@TheaterjobsInserateBundle/DataFixtures/SQL/educations.csv'
        );
        $educations = $this->csvToArray($path);
        $console = new ConsoleOutput();

        foreach ($educations as $j) {

            $education = new Education();
            $education->setTitle($j['title']);
            $education->setDescription($j['description']);
            $education->setCreatedAt(new DateTime($j['createdAt']));
            $education->setPublicationEnd(new DateTime($j['publication_end']));
            $education->setUpdatedAt(new DateTime($j['updatedAt']));
            $education->setContact($j['contact']);

//            $categ = $manager->getRepository('TheaterjobsCategoryBundle:Category')->findBy(array('slug' => $j['category']));
            $qb = $manager->createQueryBuilder();
            $categ = $qb->select('child')
                            ->from('TheaterjobsCategoryBundle:Category', 'child')
                            ->innerJoin('TheaterjobsCategoryBundle:Category', 'parent')
                            ->where('parent.id = child.parent')
                            ->andWhere('parent.slug = :parent')
                            ->setParameters(array('parent' => 'bildung'))
                            ->getQuery()->getResult();
            $category = array_rand($categ);

            $education->addCategory($categ[$category]);

            if ($j['publishedAt'] != null)
                $education->setPublishedAt(new \DateTime($j['publishedAt']));
            if ($j['archivedAt'] != null)
                $education->setArchivedAt(new \DateTime($j['archivedAt']));
            if ($this->hasReference("user_" . $j['user'])) {
                $education->setUser($this->getReference("user_" . $j['user']));
            }
            if ($this->hasReference("gratification_" . $j['gratification'])) {
                $education->setGratification($this->getReference("gratification_" . $j['gratification']));
            }

            $manager->persist($education);
        }
        $manager->flush();
    }

}