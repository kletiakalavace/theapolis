<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Theaterjobs\InserateBundle\Entity\Job;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use \DateTime;
use Carbon\Carbon;

class LoadDynamicJobData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
                '@TheaterjobsInserateBundle/DataFixtures/SQL/dynamicJobs.csv'
        );
        $jobs = $this->csvToArray($path);
        $console = new ConsoleOutput();

        foreach ($jobs as $j) {

                $job = new Job();
                $job->setTitle($j['title']);
                $job->setDescription($j['description']);
                $job->setHideOrganizationLogo($j['hideOrganizationLogo']);
                $job->setCreatedAt(Carbon::now()->addHours($j['createdAt']));
                $job->setEngagementStart(Carbon::now()->addHours($j['engagement_start']));
                $job->setEngagementEnd(Carbon::now()->addHours($j['engagement_end']));
                $job->setApplicationEnd(Carbon::now()->addHours($j['application_end']));
                $job->setPublicationEnd(Carbon::now()->addHours($j['publication_end']));
                $job->setUpdatedAt(Carbon::now()->addHours($j['updatedAt']));
                $job->setArchivedAt(Carbon::now()->addHours($j['archivedAt']));
                $job->setEmploymentDate(Carbon::now()->subHours(rand(1,240)));
                $job->setEmploymentStatus(rand(0,4));
                if ($j['publishedAt'] != null)
                    $job->setPublishedAt(Carbon::now()->addHours($j['publishedAt']));
                if ($this->hasReference("user_" . $j['users'])) {
                    $job->setUser($this->getReference("user_" . $j['users']));
                }
                if ($this->hasReference("organization_" . $j['organization'])) {
                    $job->setOrganization($this->getReference("organization_" . $j['organization']));
                }

                if ($this->hasReference("gratification_" . $j['gratification'])) {
                    $job->setGratification($this->getReference("gratification_" . $j['gratification']));
                }

                if ($this->hasReference("occupation_" . $j['ocupation'])) {
                    $job->setOccupation($this->getReference("occupation_" . $j['ocupation']));
                }

                $job->setEmploymentDate(Carbon::now()->subHours(rand(1,240)));
                $job->setEmploymentStatus(2);

                $manager->persist($job);
                $this->setReference('job_' . $job->getTitle(), $job);

        }
        $manager->flush();
    }

}
