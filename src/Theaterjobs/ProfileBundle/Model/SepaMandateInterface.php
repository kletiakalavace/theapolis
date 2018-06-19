<?php

namespace Theaterjobs\ProfileBundle\Model;

use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * The SepaMandateInterface
 *
 * Describes the SepaMandateInterface
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface SepaMandateInterface {

    /**
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * @param ProfileInterface $profile
     */
    public function setProfile(ProfileInterface $profile);
}
