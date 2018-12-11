<?php

namespace Theaterjobs\ProfileBundle\Model;

use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * The BookingInterface
 *
 * Describes the BookingInterface
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface BookingInterface {

    public function getProfile();

    public function setProfile(ProfileInterface $profile);
}
