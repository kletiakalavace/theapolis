<?php

namespace Theaterjobs\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\UserBundle\Entity\UserOrganization;
use \DateTime;
use \DateInterval;

/**
 * Datafixtures for the Organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\UserBundle\DataFixtures\ORM
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadUserOrganizationsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     *
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
        return 110;
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
//    private function csvToArray($path = '', $delimiter = ',') {
//        if (!file_exists($path) || !is_readable($path))
//            die($path . " not there");
//
//
//        $data = array();
//        if (($handle = fopen($path, 'r')) !== FALSE) {
//            $header = fgetcsv($handle, 1000, $delimiter);
//
//            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
//                if (!$header)
//                    $header = $row;
//                else
//                    $data[] = array_combine($header, $row);
//            }
//            fclose($handle);
//        }
//        return $data;
//    }
//    public function load(ObjectManager $manager) {
//        $kernel = $this->container->get('kernel');
//        $path = $kernel->locateResource(
//                '@TheaterjobsUserBundle/DataFixtures/SQL/user_organizations.csv'
//        );
//        $usersOrg = $this->csvToArray($path);
//        $console = new ConsoleOutput();
//
//        foreach ($usersOrg as $userOrg)
//        {
//            $usrOrg = new UserOrganization();
//            $usrOrg->setOrganization($this->getReference("organization_". $userOrg['tj_main_organizations_id']));
//            $usrOrg->setUser ($this->getReference('user_'. $userOrg['tj_user_users_id']));
//            $usrOrg->setConfirmed($userOrg['confirmed']);
//            $usrOrg->setRequestedAt(new Datetime($userOrg['requested_at']));
//            $usrOrg->setAdminComment($userOrg['admin_comment']);
//            $usrOrg->setGrantedAt(new Datetime($userOrg['granted_at']));
//
//            $manager->persist($usrOrg);
//        }
//        $manager->flush();
//    }

    public function load(ObjectManager $manager) {

        $userOrgs = array(
            array("userRef" => "userOrg", "orgaRef" => "Die Badische Landesbühne"),
            array("userRef" => "userOrg", "orgaRef" => "Staatsoperette Dresden"),
            array("userRef" => "memberOrg", "orgaRef" => "Die Badische Landesbühne"),
            array("userRef" => "userOrg2", "orgaRef" => "Die Badische Landesbühne"),
            array("userRef" => "userOrgOther", "orgaRef" => "Theater Plauen-Zwickau"),
            array("userRef" => "mikiko@theaterjobs.de", "orgaRef" => "Die Badische Landesbühne"),
            array("userRef" => "renate@theaterjobs.de", "orgaRef" => "Theater Plauen-Zwickau"),
            array("userRef" => "stellenmarkt@theaterjobs.de", "orgaRef" => "Staatsoperette Dresden"),
        );

        foreach ($userOrgs as $data) {
            $dateTime = new DateTime();
            $dateTime->sub(new DateInterval('P1D'));
            $userOrg = new UserOrganization();
            $userOrg->setUser($this->getReference("user_{$data['userRef']}"));
            $userOrg->setOrganization($this->getReference("organization_{$data['orgaRef']}"));
            $userOrg->setRequestedAt($dateTime);
            $dateTime->add(new DateInterval('P1D'));
            $userOrg->setGrantedAt($dateTime);

            $manager->persist($userOrg);
        }
        $manager->flush();
    }

}
