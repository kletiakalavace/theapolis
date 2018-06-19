<?php

namespace Theaterjobs\InsearteBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\OrganizationSection;
use Theaterjobs\InserateBundle\Entity\OrganizationSectionTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the organization section.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Jana Kaszas
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadOrganizationSections extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
            'Schauspiel' => array('de' => 'Schauspiel', 'sq' => 'Drama'),
            'Musiktheater' => array('de' => 'Musiktheater', 'sq' => 'Musical theatre'),
            'Tanz' => array('de' => 'Tanz', 'sq' => 'Dance'),
            'Kinder- und Jugendtheater' => array('de' => 'Kinder- und Jugendtheater', 'sq' => 'Childrens theatre'),
            'Figurentheater' => array('de' => 'Figurentheater', 'sq' => 'Puppet theatre'),
            'Orchester' => array('de' => 'Orchester', 'sq' => 'Orchestra')

        );

        foreach ($types as $name => $translations) {
            $orgaSection = new OrganizationSection();
            $orgaSection->setName($name);
            foreach ($translations as $locale => $val) {
                $orgaSection->addTranslation(
                    new OrganizationSectionTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($orgaSection);
            $manager->flush();
            $this->setReference("orgaSection_$name", $orgaSection);
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
