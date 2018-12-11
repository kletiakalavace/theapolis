<?php

namespace Theaterjobs\MembershipBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Theaterjobs\MembershipBundle\Entity\Billing;

/**
 * Description of OrderEvent
 *
 * @category Event
 * @package  TheaterjobsMainBundle\Event
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class OrderEvent extends Event {

    /**
     * @var \Theaterjobs\MembershipBundle\Entity\Billing
     */
    protected $billing;

    /**
     * @param \Theaterjobs\MembershipBundle\Entity\Billing $billing
     */
    public function __construct(Billing $billing) {
        $this->billing = $billing;
    }

    /**
     * @return \Theaterjobs\MembershipBundle\Entity\Billing
     */
    public function getBilling() {
        return $this->billing;
    }

}
