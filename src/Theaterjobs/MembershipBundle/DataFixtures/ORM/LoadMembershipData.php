<?php

namespace Theaterjobs\MembershipBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Entity\MembershipTranslation;

/**
 * Datafixtures for the Memberships.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadMembershipData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

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
        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $memberships = $this->getMemberships();

        foreach ($memberships as $title => $data) {
            $membership = new Membership();
            $membership->setTitle($title);
            $membership->setDescription($data['description']);
            $membership->setDuration($data['duration']);
            $membership->setPrice($data['price']);
            // $membership->setIsSubscription($data['isSubscription']);

            foreach ($data['translations'] as $locale => $val) {
                $repository->translate($membership, 'title', $locale, $val['title']);
                $repository->translate($membership, 'description', $locale, $val['description']);
            }

            $manager->persist($membership);
            $manager->flush();

            $this->setReference("membership_$title", $membership);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 130;
    }

    /**
     * get Memberships
     *
     * @return array
     */
    private function getMemberships() {
        $memberships = array(
            'Membership for one year' => array(
                'description' => 'This is a membership for one year',
                'price' => 49.95,
                'duration' => 'P1Y',
                'isSubscription' => false,
                'translations' => array(
                    'de' => array(
                        'title' => 'Mitgliedschaft für ein Jahr',
                        'description' => 'Das ist eine Mitgliedschaft für ein Jahr.< br/>'
                        . 'Sie läuft automatisch aus. Sie brauchen nicht zu kündigen.'
                    )
                )
            ),
//            'Membership subscription-based' => array(
//                'description' => 'This is a membership subscription based for one year',
//                'price' => 45,
//                'duration' => 'P1Y',
//                'isSubscription' => true,
//                'translations' => array(
//                    'de' => array(
//                        'title' => 'Mitgliedschaft Abo',
//                        'description' => 'Das ist eine Mitgliedschaft für ein Jahr als Abo'
//                    )
//                )
//            ),
            'Membership for three months' => array(
                'description' => 'This is a membership for three months',
                'price' => 12,
                'duration' => 'P3M',
                'isSubscription' => false,
                'translations' => array(
                    'de' => array(
                        'title' => 'Mitgliedschaft für drei Monate',
                        'description' => 'Das ist eine Mitgliedschaft für drei Monate'
                    )
                )
            ),
        );

        return $memberships;
    }

}
