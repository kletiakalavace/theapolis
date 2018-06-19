<?php

namespace Theaterjobs\ProfileBundle\Model;

use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * The DebitAccountInterface
 *
 * Describes the DebitAccountInterface
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface DebitAccountInterface {

    /**
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * @param ProfileInterface $profile
     */
    public function setProfile(ProfileInterface $profile);
}
