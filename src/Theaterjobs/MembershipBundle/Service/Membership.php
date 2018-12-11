<?php

namespace Theaterjobs\MembershipBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\Model\UserInterface;

/**
 * Membership Service
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Service
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_membership.membership")
 */
class Membership
{

    /** @DI\Inject("%theaterjobs_membership.expires_in%") */
    public $expiresIn;

    /**
     * Compares today with the membership expires at.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function expires(UserInterface $user) {
        $isExpires = false;

        $inFuture = new \DateTime();
        $inFuture->setTime(0, 0, 0); // reset time
        $inFuture->add(new \DateInterval($this->expiresIn));
        $expiresAt = $user->getMembershipExpiresAt();

        if ($expiresAt) {
            $isExpires = ($inFuture > $expiresAt);
        }

        return $isExpires;
    }

}
