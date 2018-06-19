<?php

namespace Theaterjobs\InsearteBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\OrganizationKind;
use Theaterjobs\InserateBundle\Entity\OrganizationKindTranslation;
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
class LoadOrganizationKindData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
            'Stadttheater' => array('de' => 'Stadttheater', 'sq' => 'Town theatre'),
            'Landestheater' => array('de' => 'Landestheater', 'sq' => 'County theatre'),
            'Staatstheater' => array('de' => 'Staatstheater', 'sq' => 'State theatre'),
            'Privattheater' => array('de' => 'Privattheater', 'sq' => 'Private theatre'),
            'Gastspieltheater' => array('de' => 'Gastspieltheater', 'sq' => 'Theatre without own ensemble'),
            'Freies Theater' => array('de' => 'Freies Theater', 'sq' => 'Off theatre'),
            'Ensemble' => array('de' => 'Ensemble', 'sq' => 'Ensemble'),
            'Festspiel' => array('de' => 'Festspiel', 'sq' => 'Festival')
        );

        foreach ($types as $name => $translations) {
            $orgaKind = new OrganizationKind();
            $orgaKind->setName($name);
            foreach ($translations as $locale => $val) {
                $orgaKind->addTranslation(
                    new OrganizationKindTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($orgaKind);
            $manager->flush();
            $this->setReference("orgaKind_$name", $orgaKind);
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