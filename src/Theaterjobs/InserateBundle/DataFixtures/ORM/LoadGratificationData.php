<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\Gratification;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Theaterjobs\InserateBundle\Entity\GratificationTranslation;

/**
 * Datafixtures for the Gratification.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadGratificationData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
            '> 1.000/100/10€' => array('trans' => array('de' => '> 1.000/100/10€','sq' => '> 1.000/100/10€'), 'typeOf' => 'job'),
            '> 2.000/200/20€' => array('trans' => array('de' => '> 2.000/200/20€','sq' => '> 2.000/200/20€'), 'typeOf' => 'job'),
            '> 3.000/300/30€' => array('trans' => array('de' => '> 3.000/300/30€','sq' => '> 3.000/300/30€'), 'typeOf' => 'job'),
            '> 4.000/400/40€' => array('trans' => array('de' => '> 4.000/400/40€','sq' => '> 4.000/400/40€'), 'typeOf' => 'job'),
            '> 5.000/500/50€' => array('trans' => array('de' => '> 5.000/500/50€','sq' => '> 5.000/500/50€'), 'typeOf' => 'job'),
            'Bildung, kostenpflichtig' => array('trans' => array('de' => 'Bildung, kostenpflichtig', 'sq' => 'with costs'), 'typeOf' => 'edu'),
            'Bildung, kostenfrei' => array('trans' => array('de' => 'Bildung, kostenfrei', 'sq' => 'free'), 'typeOf' => 'edu'),
            'Bildung, vergütet' => array('trans' => array('de' => 'Bildung, vergütet', 'sq' => 'paid'), 'typeOf' => 'edu'),
        );

        foreach ($types as $name => $data) {
            $gratification = new Gratification();
            $gratification->setName($name);
            $gratification->setTypeOf($data['typeOf']);
            foreach ($data['trans'] as $locale => $val) {
                $gratification->addTranslation(
                    new GratificationTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($gratification);
            $manager->flush();
            $this->setReference("gratification_$name", $gratification);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 40;
    }

}
