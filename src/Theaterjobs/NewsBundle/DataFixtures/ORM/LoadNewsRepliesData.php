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
use Theaterjobs\NewsBundle\Entity\Replies;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadNewsRepliesData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
        return 114;
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
                '@TheaterjobsNewsBundle/DataFixtures/SQL/news_replies.csv'
        );
        $replies = $this->csvToArray($path);

        $console = new ConsoleOutput();

        foreach ($replies as $reply) {
                $newsReply = new Replies();
                $news = $manager->getRepository('TheaterjobsNewsBundle:News')->find($reply['news_id']);
                $checkedBy = $manager->getRepository('TheaterjobsProfileBundle:Profile')->find($reply['checked_by']);
                $postedBy = $manager->getRepository('TheaterjobsProfileBundle:Profile')->find($reply['posted_by']);
                
                $newsReply->setArchivedAt(null);
                //$newsReply->setUseForumAlias(false);
                $newsReply->setCheckedAt(new \DateTime($reply['checked_at']));
                $newsReply->setCreatedAt(new \DateTime($reply['created_at']));
                $newsReply->setDate(new \DateTime($reply['date']));
                $newsReply->setComment($reply['comment']);
                $newsReply->setProfile($postedBy);
                $newsReply->setCheckedBy($checkedBy);
                $newsReply->setNews($news);

                $manager->persist($newsReply);
                //$this->setReference('job_' . $job->getTitle(), $job);
            
        }
        $manager->flush();
    }

}
