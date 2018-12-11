<?php

namespace Theaterjobs\MembershipBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\MembershipBundle\Entity\CountryTaxRate;

/**
 * Datafixtures for the CountryTaxRate.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MembershipBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadCountryTaxRateData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

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
     *
     */
    public function load(ObjectManager $manager) {


        $types = array(
            'AT' => 20,
            'BE' => 21,
            'BG' => 20,
            'CY' => 19,
            'CZ' => 21,
            'DE' => 19,
            'DK' => 25,
            'EE' => 20,
            'ES' => 21,
            'FI' => 24,
            'FR' => 20,
            'GB' => 20,
            'HR' => 25,
            'GR' => 23,
            'HU' => 27,
            'IE' => 23,
            'IT' => 22,
            'LT' => 21,
            'LU' => 15,
            'LV' => 21,
            'MT' => 18,
            'NL' => 21,
            'PL' => 23,
            'PT' => 23,
            'RO' => 24,
            'SE' => 25,
            'SI' => 22,
            'SK' => 20
        );

        foreach ($types as $country => $rate) {
            $taxrate = new CountryTaxRate();
            $taxrate->setCountryCode($country);
            $taxrate->setTaxRate($rate);

            $manager->persist($taxrate);
            $manager->flush();
            $this->setReference("taxrate_$country", $taxrate);
        }
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

}
