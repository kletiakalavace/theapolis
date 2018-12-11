<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\NewsBundle\Entity\Tags;
use Symfony\Component\Console\Helper\ProgressBar;
use Theaterjobs\NewsBundle\Entity\News;

/**
 * Description of MigrateCommand
 *
 */
class MigrateOrgaCommand extends MigrateCommand
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
        $this->setName('theaterjobs:migrate-orga')
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
FROM partner
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
        $this->output->writeln('Migrating Organisations');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        $batchNum = 0;
        gc_enable();
        $id = 0;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // I must create a new tag 'application info'
        $tag = new Tags();
//        $tag->setTitle('application info');
//        $tag->setCheckedAt(new \DateTime());
//
//        $em->persist($tag);
//        $em->flush();
//        $em->clear();

        
        while ($row = mysql_fetch_array($result)) {
            //$this->output->writeln("User " .$row['email']. "with ID: ".$row['uid']. " will be migrate. ");
            $orga = new Organization();
            $progress->advance();
            $batchNum++;

            $orga->setName($row['institution']);
            
            if ($row['unternehmensform'] !== NULL) {
                $form = new \Theaterjobs\InserateBundle\Entity\FormOfOrganization();
                $orgaForm = $em->getRepository('TheaterjobsInserateBundle:FormOfOrganization')->findOneBy(array('name' => $row['unternehmensform']));
                $orga->setForm($orgaForm);
            }

            $orga->setCreatedAt(new \DateTime($row['eintragdat']));
            $orga->setIsVisibleInList($row['show_in_list']);
            $orga->setIsVisibleInRegister($row['show_in_register']);
            $orga->setPath($row['logo']);
            // active
            $orga->setStatus(2);
            $this->addContactData($orga, $row);
            $this->addApplicationInfo($orga, $row['id']);
            $this->addApplicationsToNews($em, $orga, $row['id'], $tag);
            
            $em->persist($orga);
            
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
        $this->output->writeln('Orga Migration succesfull');

    }

    private function addContactData($orga, $row) {
        $adress = '';        
        $addressToConvert = '';
        $adress .= $row['institution'].  "</br>";
        if ($row['strasse']) {
             $adress .= $row['strasse'] .  "</br>";
             $addressToConvert .= $row['strasse'] .',';
        }
        if ($row['plz'] || $row['ort']) {
             $adress .= $row['plz'] . ' ' .$row['ort'].  "</br>";
             $addressToConvert .= '+' .$row['plz'] . '+' .$row['ort'] .',';
        }
        if ($row['landtree_id'] !== NULL) {
            
            // sql query to get the name from the id
            $countryName = $this->getCountryName($row['landtree_id']);
             $adress .= $countryName . "</br>";
             $addressToConvert .= '+' .$countryName;
        }

        if ($addressToConvert != '') {
            $this->convertAddressToGeodata($orga, $addressToConvert);
        }

        if ($row['tel']) {
             $adress .= "Telefon: " . $row['tel'] .  "</br>";
        }
        if ($row['fax']) {
             $adress .= "Fax: " . $row['fax'] .  "</br>";
        }
        
        if ($row['url']) {
              $adress .= "<a href=\"" .$row['url']."\" target='_blank'>".$row['url']."</a></br>";
        }

        $orga->setContactSection(new \Theaterjobs\InserateBundle\Entity\ContactSection());
        $orga->getContactSection()->setContact($adress);
        
        if ($row['email']) {
            $orga->getContactSection()->setEmail($row['email']);
        }
    }
    
    private function getCountryName($land_tree_id) {
        $query = "SELECT name FROM `tm_land_tree` WHERE id = $land_tree_id";
        
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        
        return $row['name'];
    }
    
    
    private function convertAddressToGeodata($orga, $address) {
        // Get JSON results from this request
        $apiKey = $this->getContainer()->getParameter('googleMapsApiKey');
        $api = self::googleApiUrl . "?key=$apiKey";
        $geo = file_get_contents("$api&address=".urlencode($address) . '&sensor=false');

        // Convert the JSON to an array
        $geo = json_decode($geo, true);
        //die(var_dump($geo['status']));
        if ($geo['status'] == 'OK') {
          // Get Lat & Long
          $latitude = $geo['results'][0]['geometry']['location']['lat'];
          $longitude = $geo['results'][0]['geometry']['location']['lng'];
          
          $orga->setGeolocation($latitude .','.$longitude);
        }
    }

    private function addApplicationInfo($orga, $orga_id) {
        $query = "SELECT * FROM `bewerberinfo` WHERE partner_id = $orga_id";

        $result = mysql_query($query);
        $rowAppInfo = mysql_fetch_array($result);

        if ($rowAppInfo) {
            $appInfo = $rowAppInfo['title'] . "</br></br>" . $rowAppInfo['infos'];

            $orga->setOrganisationApplicationInfoText($appInfo);
            $orga->setOrganisationApplicationInfoDate(new \DateTime($rowAppInfo['updated_at']));
        }

    }
   
}
