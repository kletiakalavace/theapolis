<?php

namespace Theaterjobs\NewsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Theaterjobs\NewsBundle\Entity\News;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use \DateTime;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadNewsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
        return 113;
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
                '@TheaterjobsNewsBundle/DataFixtures/SQL/news.csv'
        );
        $news = $this->csvToArray($path);
        $console = new ConsoleOutput();

        foreach ($news as $j) {
            $news = new News();
            $news->setPretitle($j['pretitle']);
            $news->setTitle($j['title']);
            $news->setShortDescription($j['short_description']);
            $news->setDescription($j['description']);
            $news->setCreatedAt(new \DateTime($j['created_at']));
            $news->setPublishAt(new \DateTime($j['publish_at']));
            $news->setUpdatedAt(new \DateTime($j['updated_at']));
            $news->setArchived($j['archived_at']);
            $news->setPublished($j['published']);
            //$category = $manager->getRepository('TheaterjobsCategoryBundle:Category')->findBy(array('slug' => $j['category']));
            $qb = $manager->createQueryBuilder();
            $categ = $qb->select('child')
                            ->from('TheaterjobsCategoryBundle:Category', 'child')
                            ->innerJoin('TheaterjobsCategoryBundle:Category', 'parent')
                            ->where('parent.id = child.parent')
                            ->andWhere('parent.slug = :parent')
                            ->setParameters(array('parent' => 'categories-of-news'))
                            ->getQuery()->getResult();
            
            $category = array_rand($categ);
            
            //$news->setCategory($categ[$category]);
            $orga = $manager->getRepository('TheaterjobsInserateBundle:Organization')->find($j['organization']);
            if ($orga)
                $news->addOrganization($orga);
            $user = $manager->getRepository('TheaterjobsProfileBundle:Profile')->find($j['created_by']);
            if ($user)
                $news->setCreatedBy($user);

            $manager->persist($news);
        }
        $manager->flush();
    }

}
