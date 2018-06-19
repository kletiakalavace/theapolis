<?php

namespace Theaterjobs\ProfileBundle\Model;

use Theaterjobs\UserBundle\Model\ProfileInterface;

/**
 * The UserInterface
 *
 * Describes the UserInterface
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface UserInterface
{

    public function getProfile();

    public function setProfile(ProfileInterface $profile);

    /**
     * @param string $role
     */
    public function addRole($role);

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles();

    /**
     * @param string $role
     */
    public function removeRole($role);

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
