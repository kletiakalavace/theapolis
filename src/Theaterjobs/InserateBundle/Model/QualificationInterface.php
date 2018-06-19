<?php

namespace Theaterjobs\InserateBundle\Model;

use Theaterjobs\ProfileBundle\Model\JobInterface as ProfileJob;

/**
 * The Qualification Interface
 *
 * Describes the Qualification Interface
 *
 * @category Model
 * @package  Theaterjobs\InserateBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface QualificationInterface {

    public function getJob();

    public function setJob(ProfileJob $job);
}
