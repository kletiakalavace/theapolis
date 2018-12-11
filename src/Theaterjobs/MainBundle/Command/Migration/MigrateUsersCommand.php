<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\CategoryBundle\Entity\Category;
use Theaterjobs\MainBundle\Entity\Market;
use Symfony\Component\Console\Helper\ProgressBar;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\MediaAudio;
use Theaterjobs\ProfileBundle\Entity\EmbededVideos;
use Theaterjobs\ProfileBundle\Entity\MediaPdf;
use Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo;
use Theaterjobs\UserBundle\Entity\UserOrganization;

/**
 * Description of MigrateCommand
 *
 */
class MigrateUsersCommand extends MigrateCommand
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
        $this->setName('theaterjobs:migrate-users')
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
SELECT
users.id as uid,
users.*,
person.*,
personvorname.name as name,
personnachname.name as surname ,
personmerkmale.*,
ort.ort as birthplace
FROM users
LEFT JOIN person on users.person_id = person.id
LEFT JOIN ort on ort.id = person.geburtsort_id
LEFT JOIN personvorname  on personvorname.id = person.personvorname_id
LEFT JOIN personnachname  on personnachname.id = person.personnachname_id
LEFT JOIN personmerkmale on person.personmerkmale_id = personmerkmale.id
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
        $this->output->writeln('Migrating Users');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        $batchNum = 0;
        gc_enable();
        $id = 0;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        while ($row = mysql_fetch_array($result)) {
            //$this->output->writeln("User " .$row['email']. "with ID: ".$row['uid']. " will be migrate. ");
            $user = new User();
            $progress->advance();
            $batchNum++;

            $user->setUsername($row['email']);
            $user->setEmail($row['email']);
            $user->setPlainPassword(9876);
            $user->setLastLogin(new \DateTime($row['last_login']));
            $user->setUsernameCanonical($row['email']);
            $profileAllowedTo = new ProfileAllowedTo();
            $em->persist($profileAllowedTo);

            $profile = new Profile();
            $profile->setUser($user);
            $profile->setFirstName($row['name']);
            $profile->setLastName($row['surname']);
            $profile->setAvailableLocations($row['wohnmoeglichkeit']);
            $profile->setProfileActualityDate(new \DateTime($row['aktualisiert_am']));
            $profile->setTotalViews($row['views_gesamt']);
            $profile->setProfileAllowedTo($profileAllowedTo);
            $profile->setIsPublished($row['online']);
            $profile->setBiographySection(new \Theaterjobs\ProfileBundle\Entity\BiographySection());
            $profile->setContactSection(new \Theaterjobs\ProfileBundle\Entity\ContactSection());
            $personalData = new \Theaterjobs\ProfileBundle\Entity\PersonalData();

            $profileName = $row['name'] .' '.$row['surname'];

            if ($row['augenfarbe_id']) {
                $eyeColor = $em->getRepository('TheaterjobsProfileBundle:EyeColor')->find($row['augenfarbe_id']);
                if ($eyeColor)
                    $personalData->setEyeColor($eyeColor);
            }
            if ($row['haarfarbe_id']) {
                $hairColor = $em->getRepository('TheaterjobsProfileBundle:HairColor')->find($row['haarfarbe_id']);
                if ($hairColor)
                    $personalData->setHairColor($hairColor);
            }
            if ($row['rollenalter_von'])
                $personalData->setAgeRoleFrom($row['rollenalter_von']);
            if ($row['rollenalter_bis'])
                $personalData->setAgeRoleTo($row['rollenalter_bis']);
            if ($row['koerpergroesse'])
                $personalData->setHeight($row['koerpergroesse']);
            if ($row['schuhgroesse'])
                $personalData->setShoeSize($row['schuhgroesse']);
            if ($row['konfektionsgroesse'])
                $personalData->setClothesSize($row['konfektionsgroesse']);
            if ($row['kuenstlername'] !== NULL){
                $profile->setSubtitle($row['kuenstlername']);
            }
            else {
                $profile->setSubtitle($profileName);
            }
            if ($row['untertitel'])
                $profile->setSubtitle2($row['untertitel']);
            if ($row['geb_am'])
                $personalData->setBirthDate(new \DateTime($row['geb_am']));
            if ($row['birthplace'])
                $personalData->setBirthPlace($row['birthplace']);
            if ($row['staatsangehoerigkeit_id'] == NULL) {
                $personalData->setNationality(NULL);
            } else {
                $row['staatsangehoerigkeit_id'] ? $personalData->setNationality(true) : $personalData->setNationality(false);
            }
            $personalData->setProfile($profile);
            //$profile->setPersonalData($personalData);


            $id = $row['person_id'];
            if ($id) {
                $queryBio = "SELECT * FROM person_cms WHERE person_cms.person_id = $id ORDER BY position";
                $bio = '';
                $resultBio = mysql_query($queryBio);
                while ($rowBio = mysql_fetch_array($resultBio)) {
                    if ($rowBio['text'] && $rowBio['person_cms_texte_id'] == 1) {
                        $profile->setProfileActualityText($rowBio['text']);
                    }
                    elseif ($rowBio['text'] && $rowBio['person_cms_texte_id'] == 2) {
                        $bio .= $rowBio['text'] . "</br>";
                    }
                    elseif ($rowBio['text'] && $rowBio['person_cms_texte_id'] == 3) {
                        $oldExperience = new \Theaterjobs\ProfileBundle\Entity\OldExperience();
                        $oldExperience->setExperience($rowBio['text']);
                        $oldExperience->setProfile($profile);
                        $em->persist($oldExperience);

                    }
                    elseif ($rowBio['text'] && $rowBio['person_cms_texte_id'] == 4) {
                        $oldEducation = new \Theaterjobs\ProfileBundle\Entity\OldEducation();
                        $oldEducation->setEducation($rowBio['text']);
                        $oldEducation->setProfile($profile);
                        $em->persist($oldEducation);
                    }
                    elseif ($rowBio['text'] && $rowBio['person_cms_texte_id'] == 5) {
                        $oldExtras = new \Theaterjobs\ProfileBundle\Entity\OldExtras();
                        $oldExtras->setExtras($rowBio['text']);
                        $oldExtras->setProfile($profile);
                        $em->persist($oldExtras);
                    }

                    $profile->getBiographySection()->setBiography($bio);
                }

                $queryVoice = "SELECT stimmlage.stimmlage as voice_range FROM person
                        LEFT JOIN personmerkmale on person.personmerkmale_id = personmerkmale.id
                        LEFT JOIN personmerkmale_hat_stimmlage on personmerkmale.id = personmerkmale_hat_stimmlage.personmerkmale_id
                        LEFT JOIN stimmlage on personmerkmale_hat_stimmlage.stimmlage_id = stimmlage.id
                        WHERE person.id =" . $id;

                // stimmlage
                $resultVoice = mysql_query($queryVoice);
                $rowVoice = mysql_fetch_array($resultVoice);
                if ($rowVoice['voice_range'] !== NULL) {
                    while ($rowVoice = mysql_fetch_array($resultVoice)) {
                        $voice = $em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('title' => $rowVoice['voice_range']));
                        //$this->output->writeln('Stimmlage ' .$rowVoice['voice_range']. ' has been added. ');
                        $personalData->addVoiceCategory($voice);
                    }
                }
            }
            $this->addContactData($profile, $row['person_id'], $row['adresse_id'], $row['firmenadresse_id'], $row['agenturadresse_id'],$profileName);
            $this->addOldCategories($row['person_id'], $profile, $em);
            // [Symfony\Component\HttpFoundation\File\Exception\UploadException] Reach maximum file quota 0 MB
            $this->addProfilePicture($row['person_id'], $profile, $em);
            $this->addImages($row['person_id'], $profile, $em);
            $this->addAudios($row['person_id'], $profile, $em);
            $this->addVideos($row['person_id'], $profile, $em);
            $this->addPdfs($row['person_id'], $profile, $em);
            $this->updateRoles($row['userlevel_id'],$user,$row['uid']);

            $em->persist($profile);
            $em->persist($personalData);
            $profile->setPersonalData($personalData);
            $user->setProfile($profile);
            $em->persist($user);
            $num++;
            //dump($profile);
            if ($num % 1000 == 0) {
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
        $this->output->writeln('User Migration succesfull');

    }

    private function addContactData($profile, $personId, $adresseId, $faAdresseId, $agAdresseId, $profileName) {
        $adress = '';
        if ($adresseId !== NULL) {
            $queryAddress = "SELECT strasse.strasse as street, adresse.hausnr as street_number, adresse.plz as zip, ort.ort as city,
                    tm_land_tree.name as country, adresse.link1 as link1, adresse.link2 as link2, adresse.ansichtsstatus as status,
                    lvt.telefon as pre_country_phone, vt.nummer as pre_phone_number, adresse.telefon_nummer as phone_number,
                    fvt.telefon as pre_country_fax, vf.nummer as pre_fax_number, adresse.fax_nummer as fax_number, 
                    mvt.telefon as pre_country_mobil, vm.nummer as pre_mobil_number, adresse.mobil_nummer as mobil_number
                    FROM person 
                    LEFT JOIN adresse on person.adresse_id = adresse.id
                    LEFT Join strasse on adresse.strasse_id = strasse.id
                    LEFT JOIN ort on adresse.ort_id = ort.id
                    LEFT JOIN tm_land_tree on adresse.land_id = tm_land_tree.id
                    LEFT JOIN tm_land_tree as lvt on adresse.land_vorwahl_id_tel = lvt.id
                    LEFT JOIN tm_land_tree as fvt on adresse.land_vorwahl_id_fax = fvt.id
                    LEFT JOIN tm_land_tree as mvt on adresse.land_vorwahl_id_mobil = mvt.id
                    LEFT JOIN vorwahl as vt on adresse.vorwahl_id_telefon = vt.id 
                    LEFT JOIN vorwahl as vf on adresse.vorwahl_id_fax = vf.id
                    LEFT JOIN vorwahl as vm on adresse.vorwahl_id_mobil = vm.id
                    WHERE person.id = $personId";

            $resultAddress = mysql_query($queryAddress);
            while ($rowAdd = mysql_fetch_array($resultAddress)) {
                if (in_array($rowAdd['status'], array(7,5,3))) {
                    $addressToConvert = '';
                    $adress .= $profileName.  "</br>";
                    if ($rowAdd['street'] || $rowAdd['street_number']) {
                        $adress .= $rowAdd['street'] . ' ' .$rowAdd['street_number'].  "</br>";
                        $addressToConvert .= $rowAdd['street_number'] . '+' .$rowAdd['street'] .',';
                    }
                    if ($rowAdd['zip'] || $rowAdd['city']) {
                        $adress .= $rowAdd['zip'] . ' ' .$rowAdd['city'].  "</br>";
                        $addressToConvert .= '+' .$rowAdd['zip'] . '+' .$rowAdd['city'] .',';
                    }
                    if ($rowAdd['country']) {
                        $adress .= $rowAdd['country'] . "</br>";
                        $addressToConvert .= '+' .$rowAdd['country'];
                    }

                    if ($addressToConvert != '') {
                        $this->convertAddressToGeodata($profile, $addressToConvert);
                    }

                    if ($rowAdd['pre_country_phone'] || $rowAdd['pre_phone_number'] || $rowAdd['phone_number']) {
                        $adress .= "Telefon: +" . $rowAdd['pre_country_phone'] . ' ' .$rowAdd['pre_phone_number']. ' / ' .$rowAdd['phone_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_fax'] || $rowAdd['pre_fax_number'] || $rowAdd['fax_number']) {
                        $adress .= "Fax: +" . $rowAdd['pre_country_fax'] . ' ' .$rowAdd['pre_fax_number']. ' / ' .$rowAdd['fax_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_mobil'] || $rowAdd['pre_mobil_number'] || $rowAdd['mobil_number']) {
                        $adress .= "Mobil: +" . $rowAdd['pre_country_mobil'] . ' ' .$rowAdd['pre_mobil_number']. ' / ' .$rowAdd['mobil_number'].  "</br>";
                    }
                    if ($rowAdd['link1']) {
                        $adress .= "<a href=\"" .$rowAdd['link1']."\" target='_blank'>".$rowAdd['link1']."</a></br>";
                    }
                    if ($rowAdd['link2']) {
                        $adress .= "<a href=\"" .$rowAdd['link2']."\" target='_blank'>".$rowAdd['link2']."</a></br>";
                    }
                }
            }
        }

        if ($agAdresseId !== NULL) {
            $queryAgAddress = "SELECT agenturadresse.agenturname as agency_name, strasse.strasse as street, adresse.hausnr as street_number, adresse.plz as zip, ort.ort as city, 
                tm_land_tree.name as country, adresse.link1 as link1, adresse.link2 as link2, adresse.ansichtsstatus as status,
                lvt.telefon as pre_country_phone, vt.nummer as pre_phone_number, adresse.telefon_nummer as phone_number,
                fvt.telefon as pre_country_fax, vf.nummer as pre_fax_number, adresse.fax_nummer as fax_number, 
                mvt.telefon as pre_country_mobil, vm.nummer as pre_mobil_number, adresse.mobil_nummer as mobil_number
                FROM person 
                LEFT Join agenturadresse ON person.agenturadresse_id = agenturadresse.id
                LEFT JOIN adresse on agenturadresse.adresse_id = adresse.id
                LEFT Join strasse on adresse.strasse_id = strasse.id
                LEFT JOIN ort on adresse.ort_id = ort.id
                LEFT JOIN tm_land_tree on adresse.land_id = tm_land_tree.id
                LEFT JOIN tm_land_tree as lvt on adresse.land_vorwahl_id_tel = lvt.id
                LEFT JOIN tm_land_tree as fvt on adresse.land_vorwahl_id_fax = fvt.id
                LEFT JOIN tm_land_tree as mvt on adresse.land_vorwahl_id_mobil = mvt.id
                LEFT JOIN vorwahl as vt on adresse.vorwahl_id_telefon = vt.id 
                LEFT JOIN vorwahl as vf on adresse.vorwahl_id_fax = vf.id
                LEFT JOIN vorwahl as vm on adresse.vorwahl_id_mobil = vm.id
                WHERE person.id = $personId";

            $agAdress = '';
            $resultAgAddress = mysql_query($queryAgAddress);
            while ($rowAdd = mysql_fetch_array($resultAgAddress)) {
                if (in_array($rowAdd['status'], array(7,5,3))) {
                    $agAdress .= "</br>";
                    $agAdress .= "<b>Agentur</b></br>";
                    if ($rowAdd['agency_name']) {
                        $agAdress .= $rowAdd['agency_name'] . "</br>";
                    }
                    if ($rowAdd['street'] || $rowAdd['street_number']) {
                        $agAdress .= $rowAdd['street'] . ' ' .$rowAdd['street_number'].  "</br>";
                    }
                    if ($rowAdd['zip'] || $rowAdd['city']) {
                        $agAdress .= $rowAdd['zip'] . ' ' .$rowAdd['city'].  "</br>";
                    }
                    if ($rowAdd['country']) {
                        $agAdress .= $rowAdd['country'] . "</br>";
                    }
                    if ($rowAdd['pre_country_phone'] || $rowAdd['pre_phone_number'] || $rowAdd['phone_number']) {
                        $agAdress .= "Telefon: +" . $rowAdd['pre_country_phone'] . ' ' .$rowAdd['pre_phone_number']. ' / ' .$rowAdd['phone_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_fax'] || $rowAdd['pre_fax_number'] || $rowAdd['fax_number']) {
                        $agAdress .= "Fax: +" . $rowAdd['pre_country_fax'] . ' ' .$rowAdd['pre_fax_number']. ' / ' .$rowAdd['fax_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_mobil'] || $rowAdd['pre_mobil_number'] || $rowAdd['mobil_number']) {
                        $agAdress .= "Mobil: +" . $rowAdd['pre_country_mobil'] . ' ' .$rowAdd['pre_mobil_number']. ' / ' .$rowAdd['mobil_number'].  "</br>";
                    }
                    if ($rowAdd['link1']) {
                        $agAdress .= "<a href=\"" .$rowAdd['link1']."\" target='_blank'>".$rowAdd['link1']."</a></br>";
                    }
                    if ($rowAdd['link2']) {
                        $agAdress .= "<a href=\"" .$rowAdd['link1']."\" target='_blank'>".$rowAdd['link1']."</a></br>";
                    }

                    $adress .= $agAdress;
                }
            }

        }

        if ($faAdresseId !== NULL) {
            $queryFaAddress = "SELECT firmenadresse.firmenname as companay_name, firmenadresse.abteilung as section, firmenadresse.position as postion,
                strasse.strasse as street, adresse.hausnr as street_number, adresse.plz as zip, ort.ort as city, 
                tm_land_tree.name as country, adresse.link1 as link1, adresse.link2 as link2, adresse.ansichtsstatus as status,
                lvt.telefon as pre_country_phone, vt.nummer as pre_phone_number, adresse.telefon_nummer as phone_number,
                fvt.telefon as pre_country_fax, vf.nummer as pre_fax_number, adresse.fax_nummer as fax_number, 
                mvt.telefon as pre_country_mobil, vm.nummer as pre_mobil_number, adresse.mobil_nummer as mobil_number
                FROM person 
                LEFT Join firmenadresse ON person.firmenadresse_id = firmenadresse.id
                LEFT JOIN adresse on firmenadresse.adresse_id = adresse.id
                LEFT Join strasse on adresse.strasse_id = strasse.id
                LEFT JOIN ort on adresse.ort_id = ort.id
                LEFT JOIN tm_land_tree on adresse.land_id = tm_land_tree.id
                LEFT JOIN tm_land_tree as lvt on adresse.land_vorwahl_id_tel = lvt.id
                LEFT JOIN tm_land_tree as fvt on adresse.land_vorwahl_id_fax = fvt.id
                LEFT JOIN tm_land_tree as mvt on adresse.land_vorwahl_id_mobil = mvt.id
                LEFT JOIN vorwahl as vt on adresse.vorwahl_id_telefon = vt.id 
                LEFT JOIN vorwahl as vf on adresse.vorwahl_id_fax = vf.id
                LEFT JOIN vorwahl as vm on adresse.vorwahl_id_mobil = vm.id
                WHERE person.id = $personId";

            $faAdress = '';
            $resultFaAddress = mysql_query($queryFaAddress);
            while ($rowAdd = mysql_fetch_array($resultFaAddress)) {
                if (in_array($rowAdd['status'], array(7,5,3))) {
                    $faAdress .= "</br>";
                    $faAdress .= "<b>Firma</b></br>";
                    if ($rowAdd['companay_name']) {
                        $faAdress .= $rowAdd['companay_name'] . "</br>";
                    }
                    if ($rowAdd['section']) {
                        $faAdress .= $rowAdd['section'] . "</br>";
                    }
                    if ($rowAdd['postion']) {
                        $faAdress .= $rowAdd['postion'] . "</br>";
                    }
                    if ($rowAdd['street'] || $rowAdd['street_number']) {
                        $faAdress .= $rowAdd['street'] . ' ' .$rowAdd['street_number'].  "</br>";
                    }
                    if ($rowAdd['zip'] || $rowAdd['city']) {
                        $faAdress .= $rowAdd['zip'] . ' ' .$rowAdd['city'].  "</br>";
                    }
                    if ($rowAdd['country']) {
                        $faAdress .= $rowAdd['country'] . "</br>";
                    }
                    if ($rowAdd['pre_country_phone'] || $rowAdd['pre_phone_number'] || $rowAdd['phone_number']) {
                        $faAdress .= "Telefon: +" . $rowAdd['pre_country_phone'] . ' ' .$rowAdd['pre_phone_number']. ' / ' .$rowAdd['phone_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_fax'] || $rowAdd['pre_fax_number'] || $rowAdd['fax_number']) {
                        $faAdress .= "Fax: +" . $rowAdd['pre_country_fax'] . ' ' .$rowAdd['pre_fax_number']. ' / ' .$rowAdd['fax_number'].  "</br>";
                    }
                    if ($rowAdd['pre_country_mobil'] || $rowAdd['pre_mobil_number'] || $rowAdd['mobil_number']) {
                        $faAdress .= "Mobil: +" . $rowAdd['pre_country_mobil'] . ' ' .$rowAdd['pre_mobil_number']. ' / ' .$rowAdd['mobil_number'].  "</br>";
                    }
                    if ($rowAdd['link1']) {
                        $faAdress .= "<a href=\"" .$rowAdd['link1']."\" target='_blank'>".$rowAdd['link1']."</a></br>";
                    }
                    if ($rowAdd['link2']) {
                        $faAdress .= "<a href=\"" .$rowAdd['link1']."\" target='_blank'>".$rowAdd['link1']."</a></br>";
                    }

                    $adress .= $faAdress;
                }
            }
        }

        $links = '';
        $queryLinks = "SELECT anhang.embedcode as link FROM `person_hat_anhang` 
            LEFT JOIN anhang ON person_hat_anhang.anhang_id = anhang.id 
            WHERE anhang.dateityp_id = 5 
            AND `person_id` = $personId";

        $resultLinks = mysql_query($queryLinks);
        if ($resultLinks) {
            $links .= "<b>Profil Links</b></br>";

            while ($rowAdd = mysql_fetch_array($resultLinks)) {
                $links .= "<a href=\"" .$rowAdd['link']."\" target='_blank'>".$rowAdd['link']."</a></br>";
            }
            $adress .= $links;
        }

        $profile->getContactSection()->setContact($adress);
    }

    private function addOldCategories($personId, $profile, $em) {
        $queryCats = "SELECT unterkategorien.name as cat_name FROM `person_hat_unterkategorien` 
            LEFT JOIN unterkategorien ON person_hat_unterkategorien.unterkategorien_id = unterkategorien.id
            WHERE  `person_id` = $personId";

        // we also need the root value, because we have same category names in different areas (people, work)
        $mainSection = $em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('title' => 'categories of profiles'));
        $resultCats = mysql_query($queryCats);
        if ($resultCats) {
            while ($rowCat = mysql_fetch_array($resultCats)) {
                $category = $em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('title' => $rowCat['cat_name'], 'root' => $mainSection->getRoot()));
                if ($category instanceof Category){
                    $profile->addOldCategory($category);
                    $category->addProfile($profile);
                    $em->persist($category);
                }
            }
        }
    }

    private function addImages($personId, $profile, $em) {
        $queryImages = "SELECT anhang.titel as title, anhang.dateiname as filename, anhang.urheber as owner FROM `person_hat_anhang` 
            LEFT JOIN anhang ON person_hat_anhang.anhang_id = anhang.id 
            WHERE anhang.dateityp_id = 2 
            AND `person_id` = $personId";

        $resultImages = mysql_query($queryImages);
        $subdir = 'tj_profile_profile_photos';

        if ($resultImages) {
            while ($rowImage = mysql_fetch_array($resultImages)) {
                $title = '';
                if($rowImage['title']) {
                    $title = $rowImage['title'] . ' &copy;' . $rowImage['owner'];
                } else {
                    $title = '&copy;' . $rowImage['owner'];
                }

                $image = new MediaImage();
                $image->setProfile($profile);
                $image->setTitle($title);
                $image->setPath($rowImage['filename']);
                $image->setSubdir($subdir);
                $em->persist($image);
            }
        }
    }

    private function addProfilePicture($personId, $profile, $em) {
        $queryProfilePic = "SELECT person_logo.datei as filename, person_logo.fotograf as owner, person_logo.bildtitel as title
                    FROM `person` LEFT JOIN person_logo ON person.person_logo_id = person_logo.id WHERE person.id = $personId";

        $resultProfilePic = mysql_query($queryProfilePic);
        $subdir = 'tj_profile_profile_photos';

        if ($resultProfilePic) {
            while ($rowImage = mysql_fetch_array($resultProfilePic)) {
                $title = '';
                if($rowImage['title']) {
                    $title = $rowImage['title'] . ' &copy;' . $rowImage['owner'];
                } else {
                    $title = '&copy;' . $rowImage['owner'];
                }

                $image = new MediaImage();
                $image->setProfile($profile);
                $image->setIsProfilePhoto(true);
                $image->setTitle($title);
                $image->setPath($rowImage['filename']);
                $image->setSubdir($subdir);
                $em->persist($image);
            }
        }
    }

    private function addAudios($personId, $profile, $em) {
        $queryAudios = "SELECT anhang.titel as title, anhang.dateiname as filename, anhang.beschreibung as description
                    FROM `person_hat_anhang` LEFT JOIN anhang ON person_hat_anhang.anhang_id = anhang.id 
                    WHERE anhang.dateityp_id = 4 AND `person_id` =  $personId";

        $resultAudios = mysql_query($queryAudios);
        $subdir = 'tj_profile_profile_audios';
        $defaultAudioImage = 'default_audio.jpg';

        if ($resultAudios) {
            while ($rowAudio = mysql_fetch_array($resultAudios)) {
                $title = '';
                if($rowAudio['title']) {
                    $title = $rowAudio['title'];
                }

                $audio = new MediaAudio();
                $audio->setProfile($profile);
                $audio->setTitle($title);
                $audio->setPath($rowAudio['filename']);
                $audio->setSubdir($subdir);
                $audio->setPathImage($defaultAudioImage);
                $em->persist($audio);
            }
        }
    }
    private function addPdfs($personId, $profile, $em) {
        $queryPdfs = "SELECT anhang.titel as title, anhang.dateiname as filename, anhang.beschreibung as description 
                    FROM `person_hat_anhang` LEFT JOIN anhang ON person_hat_anhang.anhang_id = anhang.id 
                    WHERE anhang.dateityp_id = 1 AND `person_id` = $personId";

        $resultPdfs = mysql_query($queryPdfs);
        $subdir = 'tj_profile_profile_pdfs';
        $defaultPdfImage = 'default_pdf.jpg';

        if ($resultPdfs) {
            while ($rowPdf = mysql_fetch_array($resultPdfs)) {
                $title = '';
                if($rowPdf['title']) {
                    $title = $rowPdf['title'];
                }

                $pdf = new MediaPdf();
                $pdf->setProfile($profile);
                $pdf->setTitle($title);
                $pdf->setPath($rowPdf['filename']);
                $pdf->setSubdir($subdir);
                $em->persist($pdf);
            }
        }
    }


    private function addVideos($personId, $profile, $em) {
        $queryVideos = "SELECT anhang.embedcode as url FROM `person_hat_anhang` 
                    LEFT JOIN anhang ON person_hat_anhang.anhang_id = anhang.id 
                    WHERE anhang.dateityp_id = 3 AND person_id =  $personId";

        $resultVideos = mysql_query($queryVideos);
        $subdir = 'tj_profile_profile_videos';

        if ($resultVideos) {
            while ($rowVideo = mysql_fetch_array($resultVideos)) {

                $video = new EmbededVideos();
                $video->setProfile($profile);
                $video->setUrl($rowVideo['url']);
                $em->persist($video);
            }
        }
    }

    private function convertAddressToGeodata($profile, $address) {
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

            $profile->getContactSection()->setGeolocation($latitude .','.$longitude);
        }
    }


    private function updateRoles($oldRoleNumber,User $user,$oldId){
        $oldRoleNumber = intval($oldRoleNumber);
        //adding role user,fos needs it by default
        $user->addRole('ROLE_USER');
        $user->setEnabled(true);
        switch ($oldRoleNumber){
            case 1:
                $user->addRole('ROLE_USER');
                break;
            case 2:
                $user->addRole('ROLE_MEMBER');
                break;
            case 3:
                //@todo,Jana will explain this better and she will find solution
                break;
            case 4:
                $user->addRole('ROLE_ADMIN');
                break;
            case 5:
                $user->addRole('ROLE_USER');
                $this->addUserOrga($user,$oldId);
                break;
            case 6:
                $user->addRole('ROLE_USER');
                $this->addUserOrga($user,$oldId);
                break;
            case 7:
                $user->addRole('ROLE_MEMBER');
                $this->addUserOrga($user,$oldId);
                break;
            case 8:
                $user->addRole('ROLE_MEMBER');
                $this->addUserOrga($user,$oldId);
                break;
            case 9:
                $user->setEnabled(false);
                break;
            default:
                echo 'No role';
        }
    }

    public function addUserOrga(User $user,$oldUserId){
        $this->host = $this->getContainer()->getParameter('old_database_host');
        $this->db = $this->getContainer()->getParameter('old_database_name');
        $this->user = $this->getContainer()->getParameter('old_database_user');
        $this->pasw = $this->getContainer()->getParameter('old_database_password');
        $link = mysql_connect($this->host, $this->user, $this->pasw);
        mysql_select_db($this->db);
        // The query
        $query = <<<EOT
SELECT institution,erteilt FROM partner_zuweisungen up inner JOIN partner p on up.partner_id = p.id
WHERE entzogen IS NULL and  user_id = $oldUserId
EOT;
        $query = str_replace(array("\r\n", "\r", "\n", "\t",), ' ', $query);
        $result = mysql_query($query);
        $em = $this->getContainer()->get('doctrine')->getManager();
        echo mysql_errno($link) . ": " . mysql_error($link);

        if ($result === FALSE) {
            $this->output->writeln("<info>No role results!</info>");
            return false;
        }
        while ($row = mysql_fetch_array($result)) {
            /**
             * @var Organization $orga
             */
            $orga = $em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name'=>$row['institution']));
            if($orga){
                $userOrganization = new UserOrganization();
                $userOrganization->setUser($user);
                $userOrganization->setGrantedAt(new \DateTime($row['erteilt']));
                $userOrganization->setOrganization($orga);
                $userOrganization->setRequestedAt(new \DateTime($row['erteilt']));
                $user->addUserOrganization($userOrganization);
                $orga->addUserOrganization($userOrganization);
                $em->persist($orga);
                $em->persist($user);
                $em->persist($userOrganization);
            }
        }
    }

}