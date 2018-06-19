<?php

namespace Theaterjobs\InserateBundle\Mailer;

use Theaterjobs\MainBundle\Utility\Traits\EmailTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * All Inserate email functions
 * @category Mailer
 * @package  Theaterjobs\ShopBundle\Mailer
 * @author   Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_inserate.mailer")
 */
class Mailer
{
    use EmailTrait;
}
