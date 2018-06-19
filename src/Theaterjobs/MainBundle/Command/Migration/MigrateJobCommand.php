<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\CategoryBundle\Entity\Category;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Carbon\Carbon;
use Theaterjobs\UserBundle\Entity\UserActivity;
use Theaterjobs\UserBundle\Entity\UserOrganization;
use Theaterjobs\NewsBundle\Entity\News;

/**
 * Description of MigrateCommand
 *
 */
class MigrateJobCommand extends MigrateCommand
{

    private $host;
    private $db;
    private $user;
    private $pasw;
    const googleApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('theaterjobs:migrate-jobs')
            ->setDescription('Migrate from old')
            ->addArgument('limit', InputArgument::REQUIRED, 'The number of records per table to migrate')
            ->addOption(
                'db', null, InputOption::VALUE_REQUIRED, 'The database to use', 'db159502_27'
            )
            ->addOption(
                'host', null, InputOption::VALUE_REQUIRED, 'The host of the database', 'localhost'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $limit = $this->input->getArgument('limit');
        $this->host = $this->getContainer()->getParameter('old_database_host');
        $this->db = $this->getContainer()->getParameter('old_database_name');
        $this->user = $this->getContainer()->getParameter('old_database_user');
        $this->pasw = $this->getContainer()->getParameter('old_database_password');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $batchSize = 30;
        $batchNum = 0;
        $link = mysql_connect($this->host, $this->user, $this->pasw);
        mysql_set_charset("utf8");
        mysql_select_db($this->db);
        // The query
        $query = <<<EOT
SELECT * 
FROM jobs
LIMIT $limit
EOT;
        //WHERE users.id = 25105
        $query = str_replace(array("\r\n", "\r", "\n", "\t",), ' ', $query);

        $result = mysql_query($query);
        echo mysql_errno($link) . ": " . mysql_error($link);

        if ($result === FALSE) {
            $this->output->writeln("<info>No results!</info>");
            return false;
        }
        $num = 0;

        $num_rows = mysql_num_rows($result);
        $this->output->writeln('');
        $this->output->writeln('Migrating Jobs');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        $batchNum = 0;
        gc_enable();
        $id = 0;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        while ($row = mysql_fetch_array($result)) {
            //$this->output->writeln("User " .$row['email']. "with ID: ".$row['uid']. " will be migrate. ");
            $job = new Job();
            $progress->advance();
            $batchNum++;

            $job->setTitle($row['bezeichnung']);
            $job->setDescription($this->createDescription($row));
            $job->setCreatedAt(new \DateTime($row['eintragdat']));
            if ($row['startprepend'] == 'next') {
                $job->setAsap(true);
            }
            if ($row['startdat'] !== NULL) {
                $job->setEngagementStart(new \DateTime($row['startdat']));
            }
            if ($row['enddat'] !== NULL) {
                $job->setEngagementEnd(new \DateTime($row['enddat']));
            }
            if ($row['fristdat'] !== NULL) {
                $job->setApplicationEnd(new \DateTime($row['fristdat']));
            }
            if ($row['loeschdat'] !== NULL) {
                $job->setPublicationEnd(new \DateTime($row['loeschdat']));
            }
            if ($row['veroeffentlicht'] !== NULL) {
                $job->setPublishedAt(new \DateTime($row['veroeffentlicht']));
            }
            if ($row['archiviert_am'] !== NULL) {
                $job->setArchivedAt(new \DateTime($row['archiviert_am']));
            }
            if ($row['geloescht_am'] !== NULL) {
                $job->setDestroyedAt(new \DateTime($row['geloescht_am']));
            }

            if ($row['aenderungs_datum'] !== NULL) {
                $job->setUpdatedAt(new \DateTime($row['aenderungs_datum']));
            }

            if ($row['mailto'] !== '') {
                $job->setEmail($row['mailto']);
            }

            $job->setStatus($this->getJobStatus($row));

            $job->setContact($this->getJobContact($row));

            $job->setGratification($this->getNewGratification($row,$em));

            if ($row['partner_id'] !== NULL) {
                $this->getOrganization($row, $em, $job);
            }

            // we only have country_id, location and zip
            if ($row['einsatzort'] !== NULL) {
                $this->addLocation($row, $job);
            }

            if ($row['user_id'] !== NULL) {
                $job->setUser($this->getUser($row, $em, $job));
            }

            if ($row['check1'] == 'J' && $row['check2'] == 'J') {
                $job->setFirstCheck($em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('id' => 2)));
            }

            // I'm not sure it's possible to migrate this
//            if ($row['copy_of'] !== NULL) {
//                $job->setParent();
//            }


            if ($row['unter_kat_id'] !== NULL) {
                $this->addOldCategories($row['unter_kat_id'], $job, $em);
            }

            if ($row['unterkat2id'] !== NULL && ($row['unter_kat_id'] !== $row['unterkat2id']) ) {
                $this->addOldCategories($row['unterkat2id'], $job, $em);
            }

            $em->persist($job);
            $em->flush();
            //$em->clear();
            $this->addJoblogs($row, $em, $job);

            $num++;
            //dump($profile);
            if ($num % 50 == 0) {
                $em->flush();
                $em->clear();
                gc_collect_cycles();
            }
            if ($num > $limit)
                break;
        //$this->output->writeln('User ' .$user->getEmail(). ' has been migrated. ');
        }

        $em->flush();
        $em->clear();
        gc_collect_cycles();
        $progress->finish();
        $this->output->writeln('Jobs Migration succesfull');

    }

    private function getJobContact($row) {
        $contact = '';
        if ($row['ansprechpartner'] !== '') {
            $contact = $row['ansprechpartner'] .'</br>';
        }

        if ($row['adr'] !== '') {
            $contact .= $row['adr'];
        }

        return $contact;
    }

    private function addOldCategories($katId, $job, $em) {
        $queryCats = "SELECT name as cat_name FROM `unterkategorien` WHERE  `id` =" . $katId;

        // we also need the root value, because we have same category names in different areas (people, work)
        $mainSection = $em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('title' => 'categories of jobs'));
        $resultCats = mysql_query($queryCats);
        if ($resultCats) {
            while ($rowCat = mysql_fetch_array($resultCats)) {
                $category = $em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('title' => $rowCat['cat_name'], 'root' => $mainSection->getRoot()));
                if ($category instanceof Category){
                    $job->addCategory($category);
                }
            }
        }
    }

    private function getOrganization($row, $em, $job) {
        $queryOrg = "SELECT institution FROM partner WHERE partner.id = " . $row['partner_id'];
        $resultOrg = mysql_query($queryOrg);
        if ($resultOrg) {
            $orgaName = mysql_fetch_array($resultOrg);
            $job->setOrganization($em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name' => $orgaName['institution'])));
        }
    }

    private function getUser($row, $em, $job) {
        $queryUser = "SELECT email FROM users WHERE users.id = " . $row['user_id'];
        $resultUser = mysql_query($queryUser);
        if ($resultUser) {
            $userEmail = mysql_fetch_array($resultUser);
            $user = $em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $userEmail[0]));
            return $user;
        }

    }

    private function getNewGratification($row,$em) {
        switch ($row['verguetungs_id']){
            case 1:
                $id = 4;
                break;
            case 2:
                $id = 3;
                break;
            case 3:
                $id = 2;
                break;
            case 4:
                $id = 1;
                break;
            case 6:
                $id = 5;
                break;
        }

        return $em->getRepository('TheaterjobsInserateBundle:Gratification')->findOneBy(array('id' => $id));
    }

    private function createDescription($row) {
        $description = '';
        if ($row['beschreibung'] !== NULL) {
            $description = $row['beschreibung'].  "</br>";
        }
        if ($row['voraussetzung'] !== NULL) {
            $description .= $row['voraussetzung'].  "</br>";
        }
        if ($row['art'] !== NULL) {
            $description .= $row['art'].  "</br>";
        }
        if ($row['sonstiges'] !== NULL) {
            $description .= $row['sonstiges'];
        }

        return $description;
    }

    private function getJobStatus($row){
        if ($row['is_entwurf'] == 'J') {
            $status = Inserate::STATUS_DRAFT;
        }
        if ($row['archiviert_am'] !== NULL) {
            $status = Inserate::STATUS_ARCHIVED;
        }
        if ($row['active'] == 'J') {
            $status = Inserate::STATUS_PUBLISHED;
        }
        if ($row['geloescht_am'] !== NULL) {
            $status = Inserate::STATUS_DELETED;
        }
        if ($row['active'] == 'N' && $row['archiviert_am'] == NULL && $row['is_entwurf'] == 'N') {
            $status = Inserate::STATUS_PENDING;
        }

        return $status;
    }

    private function addLocation($row, $job) {

        $addressToConvert = '';
        if ($row['plz_einsatzort']) {
             $addressToConvert .= $row['plz_einsatzort'] .',';
        }
        if ($row['einsatzort']) {
             $addressToConvert .= '+' .$row['einsatzort'] .',';
        }
        if ($row['land_id'] !== NULL) {
            // sql query to get the name from the id
            $countryName = $this->getCountryName($row['land_id']);
            $addressToConvert .= '+' .$countryName;
        }

        if ($addressToConvert != '') {
            $this->convertAddressToGeodata($addressToConvert, $job);
        }
    }
    
    private function getCountryName($land_tree_id) {
        $query = "SELECT name FROM `tm_land_tree` WHERE id = $land_tree_id";
        
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        
        return $row['name'];
    }
    
    
    private function convertAddressToGeodata($address, $job) {
        // Get JSON results from this request
        // API-Key AIzaSyDbn9b35ZsSpsDxaOzi9XGneFlY9FRCG-Q
        $apiKey = $this->getContainer()->getParameter('googleMapsApiKey');
        $api = self::googleApiUrl . "?key=$apiKey";
        $geo = file_get_contents("$api&address=".urlencode($address));
        // Convert the JSON to an array
        $geo = json_decode($geo, true);
        //die(var_dump($geo['status']));
        if ($geo['status'] == 'OK') {
          // Get Lat & Long
          $latitude = $geo['results'][0]['geometry']['location']['lat'];
          $longitude = $geo['results'][0]['geometry']['location']['lng'];
          
          $job->setGeolocation($latitude .','.$longitude);
        }
    }

    private function addJoblogs($row, $em, $job ) {
        // logTypeId 9 = createdAt
        // logTypeId 10 = deletedAt
        // logTypeId 13 = archivedAt
        // logTypeId 34 = archivedAt via cronjob

        $entityClass = 'Theaterjobs\InserateBundle\Entity\Job';

        $queryJoblogs = "SELECT * FROM `joblog` WHERE  `job_id` =" . $row['id'] . " AND `logtyp_id` IN (9,10,13,34)";

        $resultJoblogs = mysql_query($queryJoblogs);
        if ($resultJoblogs) {
            while ($rowLog = mysql_fetch_array($resultJoblogs)) {
                switch ($rowLog['logtyp_id']){
                    case 9:
                        $activityText = 'Job eingetragen <a href="/de/job/show/'.$job->getSlug().'">'.$job->getTitle().'</a>';
                        break;
                    case 10:
                        $activityText = 'Job gelöscht';
                        break;
                    case 13:
                        $activityText = 'Job archiviert <a href="/de/job/show/'.$job->getSlug().'">'.$job->getTitle().'</a>';
                        break;
                    case 34:
                        $activityText = 'Job per Cronjob archiviert <a href="/de/job/show/'.$job->getSlug().'">'.$job->getTitle().'</a>';
                        break;
                }


                $jobLog = new UserActivity();
                $jobLog->setCreatedBy($this->getUser($rowLog, $em, $job));
                $jobLog->setCreatedAt(new \DateTime($rowLog['timestamp']));
                $jobLog->setAdminOnly(false);
                $jobLog->setUser($this->getUser($rowLog, $em, $job));
                $jobLog->setActivityText($activityText);
                $jobLog->setEntityClass($entityClass);
                $jobLog->setEntityId($job->getId());

                $em->persist($jobLog);
            }
        }
    }

}
