<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Theaterjobs\InserateBundle\Entity\Job;
use DateTime;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 *
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link     http://www.theaterjobs.de
 */
class LoadJobData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
     * (non-PHPdoc).
     *
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
     *
     * @return bool|multitype:multitype:
     */
    private function csvToArray($path = '', $delimiter = ',') {
        if (!file_exists($path) || !is_readable($path)) {
            die($path . ' not there');
        }

        $data = array();
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, $delimiter);

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public function load(ObjectManager $manager) {
        $kernel = $this->container->get('kernel');
        $em = $this->container->get('doctrine.orm.entity_manager');

        $user = $em->getRepository("TheaterjobsUserBundle:User")->find(3);
        $path = $kernel->locateResource(
                '@TheaterjobsInserateBundle/DataFixtures/SQL/jobs.csv'
        );
        $jobs = $this->csvToArray($path);
        $console = new ConsoleOutput();

        foreach ($jobs as $j) {
            $address = new \Theaterjobs\MainBundle\Entity\Address();
            $address->setCountry('DE');
            $address->setZip(1001);

            $job = new Job();
            $job->setTitle($j['title']);
            $job->setDescription($j['description']);
            $job->setHideOrganizationLogo($j['hideOrganizationLogo']);
            $job->setFromAge($j['fromAge']);
            $job->setToAge($j['toAge']);
            $job->setCreatedAt(new DateTime($j['createdAt']));
            $job->setEngagementStart(new DateTime($j['engagement_start']));
            $job->setEngagementEnd(new DateTime($j['engagement_end']));
            $job->setApplicationEnd(new DateTime($j['application_end']));
            $job->setPublicationEnd(new DateTime($j['publication_end']));
            $job->setStatus(Inserate::STATUS_DRAFT);
            $job->setUpdatedAt(new DateTime($j['updatedAt']));
            if($j['firstCheck']){
                $job->setFirstCheck($user);
            }
            if ($j['publishedAt'] != null) {
                $job->setPublishedAt(new \DateTime($j['publishedAt']));
            }
            if ($this->hasReference('user_' . $j['users'])) {
                $job->setUser($this->getReference('user_' . $j['users']));
            }
            if ($this->hasReference('organization_' . $j['organization'])) {
                $job->setOrganization($this->getReference('organization_' . $j['organization']));
            }

            if ($this->hasReference('gratification_' . $j['gratification'])) {
                $job->setGratification($this->getReference('gratification_' . $j['gratification']));
            }

            if ($this->hasReference('occupation_' . $j['ocupation'])) {
                $job->setOccupation($this->getReference('occupation_' . $j['ocupation']));
            }

            if ($j['secondCheck']) {
                $job->setSecondCheck($this->getReference('user_admin'));
            }

            $job->setPlaceOfAction($address);
            $manager->persist($job);
            $this->setReference('job_' . $job->getTitle(), $job);
        }
        $manager->flush();
    }

}
