<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\InserateBundle\Entity\FormOfOrganization;
use Theaterjobs\InserateBundle\Entity\FormOfOrganizationTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the form of an organization.
 *
 * @category DataFixtures
 * @package  Theaterjobs\InserateBundle\DataFixtures\ORM
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadFormOfOrganization extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
            'e.V.' => array('de' => 'e.V.', 'sq' => 'inc.'),
            'GbR' => array('de' => 'GbR', 'sq' => 'BGB company'),
            'GmbH' => array('de' => 'GmbH', 'sq' => 'LLC'),
            'gGmbH' => array('de' => 'gGmbH', 'sq' => 'npLLC'),
            'Stiftung' => array('de' => 'Stifung', 'sq' => 'incorporated foundation'),
            'Körperschaft d. öffentl. Rechts' => array('de' => 'KdöR','sq' => 'PUB' ),
            'OHG' => array('de' => 'OHG', 'sq' => 'GP'),
            'KG' => array('de' => 'KG','sq' => 'LPy' ),
            'Einzelunternehmen' => array('de' => 'Einzelunternehmen', 'sq' => 'individual enterprise'),
            'AG' => array('de' => 'AG', 'sq' => 'Corp'),
            'UG' => array('de' => 'UG','sq' => 'entrepreneurial company' ),
            'Andere' => array('de' => 'Andere', 'sq' => 'other'),
            'Eigenbetrieb' => array('de' => 'Eigenbetrieb', 'sq' => 'Own operation'),
            'Regiebetrieb' => array('de' => 'Regiebetrieb', 'sq' => 'Directed operation'),
            'Anstalt des öffentlichen Rechts' => array('de' => 'Anstalt des öffentlichen Rechts', 'sq' => 'Institute of public right'),
            'gemeinnützige UG (haftungsbeschränkt)' => array('de' => 'gemeinnützige UG (haftungsbeschränkt)', 'sq' => 'non-profit UG (limited liability)')
        );

        foreach ($types as $name => $translations) {
            $formOfOrganization = new FormOfOrganization();
            $formOfOrganization->setName($name);
            foreach ($translations as $locale => $val) {
                $formOfOrganization->addTranslation(
                    new FormOfOrganizationTranslation($locale, 'name', $val)
                );
            }

            $manager->persist($formOfOrganization);
            $manager->flush();
            $this->setReference("formOfOrganization_$name", $formOfOrganization);
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

