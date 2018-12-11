<?php

namespace Theaterjobs\MembershipBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;

/**
 * Datafixtures for the BookingStati.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MembershipBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadBillingStatiData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     *
     */
    public function load(ObjectManager $manager) {
        $names = array(
            BillingStatus::OPEN,
            BillingStatus::PENDING,
            BillingStatus::COMPLETE,
            BillingStatus::STORNO
        );

        foreach ($names as $name) {
            $status = new BillingStatus();
            $status->setName($name);
            $manager->persist($status);
            $manager->flush();
            $this->setReference("billingstatus_$name", $status);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 30;
    }

}
