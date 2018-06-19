<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\ProfileBundle\Entity\HairColor;
use Theaterjobs\ProfileBundle\Entity\HairColorTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the profile hair color.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Malvin Dake
 * @author   Vilson Duka
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadHairColorData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
            'blond' => array('de' => 'blond', 'sq' => 'blonde'),
            'dunkelblond' => array('de' => 'dunkelblond','sq' => 'dark blonde' ),
            'rot' => array('de' => 'rot', 'sq' => 'red'),
            'braun' => array('de' => 'braun', 'sq' => 'brown'),
            'grau' => array('de' => 'grau','sq' => 'gray' ),
            'schwarz' => array('de' => 'schwarz', 'sq' => 'black'),
            'Glatze' => array('de' => 'Glatze', 'sq' => 'bold')
        );

        foreach ($types as $name => $translations) {
            $hairColor = new HairColor();
            $hairColor->setName($name);
            foreach ($translations as $locale => $val) {
                $hairColor->addTranslation(
                    new HairColorTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($hairColor);
            $manager->flush();
            $this->setReference("hairColor_$name", $hairColor);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 9;
    }

}
