<?php

namespace Theaterjobs\MembershipBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Knp\Snappy\Pdf;
use Theaterjobs\MembershipBundle\Entity\SepaMandate;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Description of Sepa
 *
 * @category CATEGORY
 * @package  Theaterjobs\MembershipBundle\Sepa
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_membership.sepa")
 */
class Sepa {

    const SEPA_ERROR_BIC_NOT_SUPPORTED = -1;
    const SEPA_ERROR_BIC_INVALID = -2;

    /**
     * The username for the libSepa API
     *
     * @DI\Inject("%theaterjobs_membership.sepaapi_username%")
     * @var string
     */
    public $sepaapiUsername;

    /**
     * The code for the libSepa API
     *
     * @DI\Inject("%theaterjobs_membership.sepaapi_code%")
     * @var string
     */
    public $sepaapiCode;

    /**
     * Our IBAN
     *
     * @DI\Inject("%theaterjobs_membership.sepa_iban%")
     * @var string
     */
    public $sepaIban;

    /**
     * Our BIC
     *
     * @DI\Inject("%theaterjobs_membership.sepa_bic%")
     * @var string
     */
    public $sepaBic;

    /**
     * Our SEPA name
     *
     * @DI\Inject("%theaterjobs_membership.sepa_name%")
     * @var string
     */
    public $sepaName;

    /**
     * Our SEPA creditor id
     *
     * @DI\Inject("%theaterjobs_membership.sepa_creditor_id%")
     * @var string
     */
    public $sepaCreditorId;

    /**
     * twig render
     *
     * @DI\Inject("twig")
     * @var Pdf
     */
    public $twig;
    
    /**
     * Check the IBAN via libSEPA
     *
     * @param string $iban
     * @return boolean
     */
    public function checkIban($iban) {
        return \SEPA::IBAN_check($iban);
    }

    /**
     * Check the BIC via libSEPA
     *
     * @param string $bic
     * @return int | boolean errorCode
     */
    public function checkBic($bic, $iban) {
        $bicGenerate = substr_replace(\SEPA::IBAN_getBIC($iban), 'XXX', -3);
        $bicRepalce = substr_replace($bic, 'XXX', -3);
        if ($bicGenerate == $bicRepalce) {
            $name = \SEPA::BIC_getBankName($bicRepalce);
            return $name != NULL ?: Sepa::SEPA_ERROR_BIC_INVALID;
        }
        return false;
    }

    /**
     * 
     * @param string $iban
     * @return array
     */
    public function generateBic($iban) {
        $iban = str_replace(" ", "", $iban);
        $bicGenerate = substr_replace(\SEPA::IBAN_getBIC($iban), 'XXX', -3);
        
        $name = \SEPA::BIC_getBankName($bicGenerate);
        
        return array('bic'=>$bicGenerate, 'bankName' => $name);
    }

    /**
     * Generates sepa
     * @param Profile $profile
     * @param string $request
     * @return SepaMandate
     */
    public function generateSepa($profile, $request = "127.0.0.1")
    {
        $sepaMandate = new SepaMandate();
        // we start counting by 1
        $count = 1;
        $count += $profile->getSepaMandates()->count();
        $mandateReference = $profile->getId() . "-$count-THEAPOLIS";
        $sepaMandate->setProfile($profile);
        $sepaMandate->setIpAddress($request);
        $sepaMandate->setMandatereference($mandateReference);
        $sepaMandate->setPath($mandateReference."pdf");
        return $sepaMandate;
    }
}
