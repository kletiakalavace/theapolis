<?php

namespace Theaterjobs\MembershipBundle\Model;

/**
 * The User Interface
 *
 * Describes the User Interface
 *
 * @category Model
 * @package  Theaterjobs\MembershipBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */

/**
 *
 * @author crak
 */
interface UserInterface
{

    /**
     * Set membershipExpiresAt
     *
     * @param \DateTime $membershipExpiresAt
     * @return User
     */
    public function setMembershipExpiresAt($membershipExpiresAt);

    /**
     * Get membershipExpiresAt
     *
     * @return \DateTime
     */
    public function getMembershipExpiresAt();
}
