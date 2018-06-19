<?php

namespace Theaterjobs\MainBundle\Mailer;

use Theaterjobs\MainBundle\Utility\Traits\EmailTrait;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * General Class to send email
 * BaseMailer Service
 * @category Mailer
 * @package  Theaterjobs\ShopBundle\Mailer
 * @author   Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("base_mailer")
 */
class Mailer
{
    use EmailTrait;

    /** @DI\Inject("doctrine.orm.entity_manager")  */
    public $em;

    /**
     * Check if email is temporary or false.
     * @param $email
     * @return mixed
     * @TODO Refactor or change
     */
    public function checkFalseEmail($email)
    {
        try {
            $splitedEmail = explode("@", $email);
            $domain = $splitedEmail[1];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.mogelmail.de/q/' . $domain
            ));
            $resp = curl_exec($curl);
            curl_close($curl);
            return $resp == 1;
        } catch (\Exception $e) {
            return true;
        }
    }
}
