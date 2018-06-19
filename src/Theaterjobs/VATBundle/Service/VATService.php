<?php

namespace Theaterjobs\VATBundle\Service;

use Theaterjobs\VATBundle\Exception\VATException;
use Theaterjobs\VATBundle\Exception\InvalidCountryCodeException;
use Theaterjobs\VATBundle\Exception\InvalidVATNumberException;
use Theaterjobs\VATBundle\Exception\InvalidVATValidationException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * VATService
 *
 * Based on https://github.com/ruudk/VATBundle
 *
 * @category Service
 * @package  Theaterjobs\VATBundle\Service
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\Service("theaterjobs_vat.vatservice")
 */
class VATService {

    /**
     * The Abbreviation of the EU countries.
     * They are used in Symfony2 Country Form and conform to ISO-3166.
     *
     * @var array
     */
    static $validCountries = array(
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE',
        'DK', 'EE', 'ES', 'FI', 'FR', 'GB',
        'GR', 'HR', 'HU', 'IE', 'IT', 'LT',
        'LU', 'LV', 'MT', 'NL', 'PL', 'PT',
        'RO', 'SE', 'SI', 'SK'
    );

    /**
     * Mapping of European Commision.
     *
     * The European Commission differs from the ISO-3166. This maps the difference.
     *
     * @see http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Imperfect_implementations
     * @var array
     */
    static $euCountryMapping = array(
        'AT' => 'AT',
        'BE' => 'BE',
        'BG' => 'BG',
        'CY' => 'CY',
        'CZ' => 'CZ',
        'DE' => 'DE',
        'DK' => 'DK',
        'EE' => 'EE',
        'ES' => 'ES',
        'FI' => 'FI',
        'FR' => 'FR',
        'GB' => 'UK',
        'GR' => 'EL',
        'HR' => 'HR',
        'HU' => 'HU',
        'IE' => 'IE',
        'IT' => 'IT',
        'LT' => 'LT',
        'LU' => 'LU',
        'LV' => 'LV',
        'MT' => 'MT',
        'NL' => 'NL',
        'PL' => 'PL',
        'PT' => 'PT',
        'RO' => 'RO',
        'SE' => 'SE',
        'SI' => 'SI',
        'SK' => 'SK'
    );

    public function validate($vatNumber) {
        $array = explode(substr($vatNumber, 0, 2), $vatNumber);
        $countryCode = substr($vatNumber, 0, 2);
        $number = $array[1];
        if (!preg_match('/^[A-Z]{2}$/', $countryCode)) {

            throw new InvalidCountryCodeException(
            'The countrycode is not valid. It must be in format [A-Z]{2}'
            );
        }

        $countryMappedCode = self::$euCountryMapping[$countryCode];

        if ($countryMappedCode) {
            if (!preg_match('/^[A-Z]{2}[0-9A-Za-z]{8,12}$/', $vatNumber)) {
                throw new InvalidVATNumberException(
                'The VAT number is not valid. It must be in format [A-Z]{2}[0-9A-Za-z]{8,12}'
                );
            }
            $this->checkWithVIES($countryCode, $number);
        }
    }

    protected function checkWithVIES($countryCode, $vatNumber) {

        try {
            $client = new \SoapClient(__DIR__ . '/../Resources/wsdl/checkVatService.wsdl', array(
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'style' => SOAP_DOCUMENT,
                'encoding' => SOAP_LITERAL,
                'location' => 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService',
                'trace' => 1
            ));

            $result = $client->checkVat(array(
                'countryCode' => $countryCode,
                'vatNumber' => $vatNumber
            ));

            if ((int) $result->valid !== 1) {
                throw new InvalidVATValidationException('The VAT number is not valid against view');
            }
        } catch (\SoapFault $exception) {
            throw new VATException($exception);
        }
    }

}
