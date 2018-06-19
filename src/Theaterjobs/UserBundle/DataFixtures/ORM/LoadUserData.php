<?php

namespace Theaterjobs\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Entity\BiographySection;
use Theaterjobs\ProfileBundle\Entity\ContactSection;
use Theaterjobs\ProfileBundle\Entity\QualificationSection;
use Theaterjobs\ProfileBundle\Entity\SkillSection;
use Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 50;
    }

    private function csvToArray($path = '', $delimiter = ',')
    {
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

    public function load(ObjectManager $manager)
    {

        $kernel = $this->container->get('kernel');
        $profilePath = $kernel->locateResource('@TheaterjobsUserBundle/DataFixtures/SQL/profile.csv');
        $biographyPath = $kernel->locateResource('@TheaterjobsUserBundle/DataFixtures/SQL/section_biography.csv');
        $contactPath = $kernel->locateResource('@TheaterjobsUserBundle/DataFixtures/SQL/section_contact.csv');
        $profiles = $this->csvToArray($profilePath);
        $biography = $this->csvToArray($biographyPath);
        $contact = $this->csvToArray($contactPath);

        $users = array(
            array("username" => "user", "email" => "user@user.com", "roles" => array("ROLE_USER")),
            array("username" => "member", "email" => "member@member.com", "roles" => array("ROLE_MEMBER")),
            array("username" => "admin", "email" => "admin@admin.com", "roles" => array("ROLE_ADMIN")),
            array("username" => "fenner@theaterjobs.de", "email" => "fenner@theaterjobs.de", "roles" => array("ROLE_ADMIN")),
            array("username" => "userOrg", "email" => "userOrg@userOrg.com", "roles" => array("ROLE_USER")),
            array("username" => "memberOrg", "email" => "memberOrg@memberOrg.com", "roles" => array("ROLE_MEMBER")),
            array("username" => "userOrg2", "email" => "userOrg2@userOrg2.com", "roles" => array("ROLE_USER")),
            array("username" => "userOrgOther", "email" => "userOrgOther@userOrgOther.com", "roles" => array("ROLE_USER")),
            array("username" => "renate@theaterjobs.de", "email" => "renate@theaterjobs.de", "roles" => array("ROLE_ADMIN")),
            array("username" => "nicole@theaterjobs.de", "email" => "nicole@theaterjobs.de", "roles" => array("ROLE_USER")),
            array("username" => "kay@theaterjobs.de", "email" => "kay@theaterjobs.de", "roles" => array("ROLE_ADMIN")),
            array("username" => "mikiko@theaterjobs.de", "email" => "mikiko@theaterjobs.de", "roles" => array("ROLE_ADMIN")),
            array("username" => "heim-martin@gmx.de", "email" => "heim-martin@gmx.de", "roles" => array("ROLE_MEMBER")),
            array("username" => "marina.erdmann@web.de", "email" => "marina.erdmann@web.de", "roles" => array("ROLE_MEMBER")),
            array("username" => "stellenmarkt@theaterjobs.de", "email" => "stellenmarkt@theaterjobs.de", "roles" => array("ROLE_ADMIN")),
            array("username" => "info@cyrus-rahbar.de", "email" => "info@cyrus-rahbar.de", "roles" => array("ROLE_MEMBER")),
            array("username" => "felixfrenken@web.de", "email" => "felixfrenken@web.de", "roles" => array("ROLE_MEMBER")),
            array("username" => "post@frederikbeyer.de", "email" => "post@frederikbeyer.de", "roles" => array("ROLE_MEMBER")),
            array("username" => "midelemarre@hotmail.com", "email" => "idelemarre@hotmail.com", "roles" => array("ROLE_MEMBER")),
            array("username" => "florian.schmitt@spotlightevents.eu", "email" => "florian.schmitt@spotlightevents.eu", "roles" => array("ROLE_MEMBER")),
            array("username" => "katharinaforster@gmx.net", "email" => "katharinaforster@gmx.net", "roles" => array("ROLE_MEMBER")),
            array("username" => "m.strahl@gmx.ch", "email" => "m.strahl@gmx.ch", "roles" => array("ROLE_MEMBER")),
        );

        //$skillPrivacyArray = array(0, 2, 7, 8, 9);
        $userManager = $this->container->get('fos_user.user_manager');
        $datetime = new \DateTime("2014-01-01 08:20:55");
        $i = 0;
        foreach ($users as $data) {
            $user = $userManager->createUser();
            $user->setEnabled(true);
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPlainPassword('rambazamba');
            $user->setNetworkLastVisit($datetime);
            $user->setEducationLastVisit($datetime);
            $user->setProfileLastVisit($datetime);
            $user->setForumLastVisit($datetime);
            $user->setJobLastVisit($datetime);
            foreach ($data['roles'] as $role) {
                $user->addRole($role);
            }

            $profile = new Profile();
            $profile->setFirstName($profiles[$i]['firstName']);
            $profile->setLastName($profiles[$i]['lastName']);
            $profile->setSubtitle($profiles[$i]['firstName'] . ' ' . $profiles[$i]['lastName']);
            $profile->setIsPublished($profiles[$i]['isPublished']);
            $profile->setShowWizard($profiles[$i]['showWizard']);
            $profile->setUsedSpace(0);
            $profile->setInAdminCheckList(0);
            $profile->setIsRevokedBefore(false);

            if ($i >= 12) {
                $nr = $i - 12;

                $biographySection = new BiographySection();
                $biographySection->setBiography($biography[$nr]['biography']);

                $profile->setBiographySection($biographySection);

                $contactSection = new ContactSection();
                $contactSection->setContact($contact[$nr]['contact']);
                $contactSection->setGeolocation($contact[$nr]['geolocation']);

                $profile->setContactSection($contactSection);

                $qualificationSection = new QualificationSection();

                $profile->setQualificationSection($qualificationSection);

                $qualification = $profile->getQualificationSection();
                $newQualification = new \Theaterjobs\ProfileBundle\Entity\Qualification();

                if ($profiles[$i]['profession'] != '')
                    $newQualification->setProfession($profiles[$i]['profession']);

                if ($profiles[$i]['category'] != '') {
                    $qb = $manager->createQueryBuilder();

                    $categs = $qb->select('child.id')
                        ->from('TheaterjobsCategoryBundle:Category', 'child')
                        ->innerJoin('TheaterjobsCategoryBundle:Category', 'parent')
                        ->where('parent.id = child.parent')
                        ->andWhere('parent.slug = :parent')
                        ->setParameters(array('parent' => 'categories-of-profiles'))
                        ->getQuery()->getResult();

                    $qb = $manager->createQueryBuilder();

                    $categ2 = $qb->select('child')
                        ->from('TheaterjobsCategoryBundle:Category', 'child')
                        ->innerJoin('TheaterjobsCategoryBundle:Category', 'parent')
                        ->where('parent.id = child.parent')
                        ->andWhere('parent.id IN(:parents)')
                        ->setParameters(array('parents' => $categs))
                        ->getQuery()->getResult();

                    $category = array_rand($categ2);

                    $newQualification->setCategories($categ2[$category]);
                }

                if ($profiles[$i]['education_type'] != '')
                    $newQualification->setEducationtype($profiles[$i]['education_type']);

                if ($profiles[$i]['organization_id'] != '')
                    $newQualification->setOrganizationRelated(null);

                if ($profiles[$i]['start_date'] != '')
                    $newQualification->setStartDate($profiles[$i]['start_date']);
                if ($profiles[$i]['finished'] != '')
                    $newQualification->setFinished($profiles[$i]['finished']);
                if ($profiles[$i]['finished'] == 1 && intval($profiles[$i]['end_date']) > 0) {
                    $newQualification->setEndDate($profiles[$i]['end_date']);
                }
                if ($profiles[$i]['experience'] != '')
                    $newQualification->setExperience($profiles[$i]['experience']);
                if ($profiles[$i]['management'] != '')
                    $newQualification->setManagmentResponsibility($profiles[$i]['management']);

                $qualification->addQualification($newQualification);

                $skillSection = new SkillSection();

                $driveLicense = $manager->getRepository('TheaterjobsCategoryBundle:Category')->findOneBy(array('slug' => strtolower($profiles[$i]['driving_license'])));
                $skillSection->addDriveLicense($driveLicense);
                $profile->setSkillSection($skillSection);
            }

            $profileAllowedTo = new ProfileAllowedTo();
            $profile->setProfileAllowedTo($profileAllowedTo);

            $user->setProfile($profile);
            $profile->setUser($user);
            $manager->persist($profile);
            $manager->flush();
            $userManager->updateUser($user);

            $this->setReference("user_{$data['username']}", $user);
            $i++;
        }
    }

}
