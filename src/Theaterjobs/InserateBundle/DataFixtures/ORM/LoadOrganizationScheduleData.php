<?php

namespace Theaterjobs\InsearteBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\OrganizationSchedule;
use Theaterjobs\InserateBundle\Entity\OrganizationScheduleTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the organization schedule dropdown.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Jana Kaszas
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadOrganizationScheduleData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * The container
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * Set the container interface
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * Load the fixtures
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager) {
        $translatable = $this->container->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale('en');

        $types = array(
            'Repertoire-Spielbetrieb' => array('de' => 'Repertoire-Spielbetrieb', 'sq' => 'Repertoire'),
            'En-Bloc-Spielbetrieb' => array('de' => 'En-Bloc-Spielbetrieb', 'sq' => 'En Bloc'),
            'En-suite-Spielbetrieb' => array('de' => 'En-suite-Spielbetrieb', 'sq' => 'En Suite'),
            'kein regelmäßiger Spielbetrieb' => array('de' => 'kein regelmäßiger Spielbetrieb', 'sq' => 'No continuous schedule')
        );

        foreach ($types as $name => $translations) {
            $orgaSchedule = new OrganizationSchedule();
            $orgaSchedule->setName($name);
            foreach ($translations as $locale => $val) {
                $orgaSchedule->addTranslation(
                    new OrganizationScheduleTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($orgaSchedule);
            $manager->flush();
            $this->setReference("orgaSchedule_$name", $orgaSchedule);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 50;
    }

}
