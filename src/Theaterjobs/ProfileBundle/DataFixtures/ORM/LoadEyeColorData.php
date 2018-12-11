<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\ProfileBundle\Entity\EyeColor;
use Theaterjobs\ProfileBundle\Entity\EyeColorTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the profile eye color.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Malvin Dake
 * @author   Vilson Duka
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadEyeColorData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
            'braun' => array('de' => 'braun', 'sq' => 'brown'),
            'blau' => array('de' => 'blau', 'sq' => 'blue'),
            'grün' => array('de' => 'grün', 'sq' => 'green'),
            'grau' => array('de' => 'grau', 'sq' => 'grey'),
            'grau-blau' => array('de' => 'grau-blau', 'sq' => 'grey-blue'),
            'grau-grün' => array('de' => 'grau-grün', 'sq' => 'grey-green'),
            'grau-braun' => array('de' => 'grau-braun','sq' => 'grey-brown' ),
            'grün-blau' => array('de' => 'grün-blau', 'sq' => 'green-blue'),
            'grün-braun' => array('de' => 'grün-braun','sq' => 'green-brown' ),
            'blau-braun' => array('de' => 'blau-braun', 'sq' => 'blue-brown')
        );

        foreach ($types as $name => $translations) {
            $eyeColor = new EyeColor();
            $eyeColor->setName($name);
            foreach ($translations as $locale => $val) {
                $eyeColor->addTranslation(
                    new EyeColorTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($eyeColor);
            $manager->flush();
            $this->setReference("eyeColor_$name", $eyeColor);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 8;
    }

}
