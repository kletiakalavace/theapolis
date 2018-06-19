<?php

namespace Theaterjobs\MembershipBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\MembershipBundle\Entity\PaymentmethodTranslation;

/**
 * Datafixtures for the Paymentmethods.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MembershipBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadPaymentmethodData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
        $translatable = $this->container->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale('en');

        $types = array(
            'Vorkasse' => array(
                'short' => Paymentmethod::PREPAYMENT,
                'isActive' => false,
                'price' => 0.0,
                'translations' => array('de' => 'Vorkasse', 'sq' => 'Prepayment'),
            ),
            'Lastschrift' => array(
                'short' => Paymentmethod::DIRECT_DEBIT,
                'isActive' => true,
                'price' => 0.0,
                'translations' => array('de' => 'Lastschrift', 'sq' => 'Direct debit'),
                'isSubscription' => 1
            ),
            'PayPal' => array(
                'short' => Paymentmethod::PAYPAL,
                'isActive' => true,
                'price' => 2.00,
                'translations' => array('de' => 'PayPal', 'sq' => 'PayPal'),
                'isSubscription' => 0
            ),
            'Sofort überweisung' => array(
                'short' => Paymentmethod::IMMEDIATELY_TRANSFER,
                'isActive' => false,
                'price' => 0.0,
                'translations' => array('de' => 'Sofort überweisung', 'sq' => 'Immediately transfer'),
            ),
            'Sofort' => array(
                'short' => Paymentmethod::SOFORT,
                'isActive' => true,
                'price' => 0.0,
                'translations' => array('de' => 'Sofort', 'sq' => 'Sofort'),
                'isSubscription' => 0
            ),
        );

        foreach ($types as $title => $data) {
            $payment = new Paymentmethod();
            $payment->setTitle($title);
            $payment->setShort($data['short']);
            $payment->setPrice($data['price']);
            $payment->setIsActive($data['isActive']);
            foreach ($data['translations'] as $locale => $val) {
                $payment->addTranslation(
                    new PaymentmethodTranslation($locale,'title', $val)
                );
            }
            $manager->persist($payment);
            $manager->flush();
            $this->setReference("paymentmethod_$title", $payment);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 100;
    }

}
