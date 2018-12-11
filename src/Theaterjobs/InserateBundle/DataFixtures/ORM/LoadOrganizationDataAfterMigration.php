<?php
//
//namespace Theaterjobs\InserateBundle\DataFixtures\ORM;
//
//use Doctrine\Common\DataFixtures\AbstractFixture;
//use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
//use Doctrine\Common\Persistence\ObjectManager;
//use Symfony\Component\DependencyInjection\ContainerAwareInterface;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\Console\Output\ConsoleOutput;
//use Symfony\Component\Console\Formatter\OutputFormatterStyle;
//use Theaterjobs\InserateBundle\Entity\Organization;
//use Theaterjobs\MainBundle\Entity\Address;
//
///**
// * Datafixtures for the Organization after Migration.
// *
// * @category DataFixtures
// * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
// * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
// * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
// * @link     http://www.theaterjobs.de
// */
//class LoadOrganizationDataAfterMigration extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {
//
//    /**
//     * @var ContainerInterface
//     */
//    private $container;
//
//    /**
//     * {@inheritDoc}
//     */
//    public function setContainer(ContainerInterface $container = null) {
//        $this->container = $container;
//    }
//
//    /**
//     * Load organization datas
//     */
//    public function load(ObjectManager $manager) {
//        $kernel = $this->container->get('kernel');
//        $path = $kernel->locateResource(
//                '@TheaterjobsInserateBundle/DataFixtures/SQL/tj_inserate_organizations.csv'
//        );
//
//        $orgas = $this->csvToArray($path);
//        $console = new ConsoleOutput();
//        $console->writeln("Ok,will take some time to load " . count($orgas) . " organizations");
//
//        $exitOn = 1000;
//        $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
//        $console->getFormatter()->setStyle('fire', $style);
//        $console->writeln("<fire>FOR NOW WE ONLY USE $exitOn organizations.");
//        $console->writeln("SO FIX THIS IF YOU NEED MORE!!!</fire>");
//        $console->writeln("...please be patient!");
//
//        $i = 0;
//
//        foreach ($orgas as $orga) {
//            if ($exitOn == $i)
//                break;
//
//            $organization = new Organization();
//            $organization->setContactSection($orga['tj_inserate_contact_section_id']);
//            $organization->setForm($orga['tj_inserate_form_of_organizations_id']);
//            $organization->setOrganizationSchedule($orga['organization_schedule']);
//            $organization->setMergedTo($orga['merge_to_id']);
//            $organization->setAddress($orga['tj_inserate_addresses_id']);
//            $organization->setPath($orga['path']);
//            $organization->setName($orga['name']);
//            $organization->setDescription($orga['description']);
//            $organization->setSlug($orga['slug']);
//            $organization->setIsVisibleInList($orga['is_visible_in_list']);
//            $organization->setIsVio($orga['is_vio']);
//            $organization->setIsVisibleInRegister($orga['is_visible_in_register']);
//            $organization->setCreatedAt($orga['created_at']);
//            $organization->setUpdatedAt($orga['updated_at']);
//            $organization->setDestroyedAt($orga['destroyedAt']);
//            $organization->setArchivedAt($orga['archived_at']);
//            $organization->setNotReachableAt($orga['notReachableAt']);
//            $organization->setWageFrom($orga['wage_from']);
//            $organization->setWageTo($orga['wage_to']);
//            $organization->setOrganizationOwner($orga['organization_owner']);
//            $organization->setOrchestraClass($orga['OrchestraClass']);
//            $organization->setStaff($orga['staff']);
//            $organization->setGeolocation($orga['geolocation']);
//            $organization->setOrganisationApplicationInfoText($orga['application_info_text']);
//            $organization->setOrganisationApplicationInfoDate($orga['application_info_date']);
//            $organization->setStatus($orga['status']);
//
//            $manager->persist($organization);
//
//            $this->setReference("organization_{$orga['institution']}", $organization);
//            $i++;
//        }
//        $manager->flush();
//    }
//
//    /**
//     * (non-PHPdoc)
//     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
//     *
//     * @return number
//     */
//    public function getOrder() {
//        return 60;
//    }
//
//    /**
//     * Reads data from csv and returns an php array.
//     *
//     * The first line of the csv hast to contain the fieldnames.
//     *
//     * @param string $filename
//     * @param string $delimiter
//     * @return boolean|multitype:multitype:
//     */
//    private function csvToArray($path = '', $delimiter = '|') {
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
//                    print_r($header);
//                print_r($row);
//                    $data[] = array_combine($header, $row);
//            }
//            fclose($handle);
//        }
//        return $data;
//    }
//
//}
