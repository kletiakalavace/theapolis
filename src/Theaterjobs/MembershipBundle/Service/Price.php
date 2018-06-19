<?php

namespace Theaterjobs\MembershipBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\CountryTaxRate;
use Theaterjobs\VATBundle\Service\VATService;

/**
 * BookingCalculator Service
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Service
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_membership.price")
 */
class Price
{

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * @var \Theaterjobs\VATBundle\Service\VATService
     * @DI\Inject("theaterjobs_vat.vatservice")
     */
    public $vat;

    /**
     * Determines the CountryTaxRate
     *
     * Depending on the Country of the BillingAddress the CountryTaxRate will be returned.
     * If the Country is outside of the EU null will be returned.
     * If the Country is inside the EU it depends wether the BillingAddress has
     * a VatId or not.
     *
     * @param Booking $booking
     * @return \Theaterjobs\MembershipBundle\Entity\CountryTaxRate
     */
    public function getCountryTaxRate(Booking $booking) {
        $profile = $booking->getProfile();
        $billingAddress = $profile->getBillingAddress();
        $country = $billingAddress->getCountry();
        $euCountries = VATService::$validCountries;

        // Is inside EU?
        if (in_array($country, $euCountries)) {
            // With VatID?
            if ($billingAddress->getVatId()) {
                $country = 'DE';
            }
            return $this->em->getRepository(CountryTaxRate::class)->findOneByCountryCode($country);
        }
        return null;
    }

    /**
     * Calc the Gross
     *
     * @param Booking $booking
     * @return double
     */
    public function getSumGross(Booking $booking) {
        return $booking->getMembership()->getPrice();
    }

    /**
     * Calc the Vat.
     *
     * @param Booking $booking
     * @return double
     */
    public function getSumVat(Booking $booking) {
        return $this->getSumGross($booking) - $this->getSumNet($booking);
    }

    /**
     * Calc the Net
     *
     * @param Booking $booking
     * @return double
     */
    public function getSumNet(Booking $booking) {
        // Get tax for this booking based on his country
        $taxRate = $this->getCountryTaxRate($booking);
        // Total fee (gross | brutto)
        $gross = $this->getSumGross($booking);
        // Return net
        return $this->getNet($gross, $taxRate);
    }

    /**
     * Get the price of the Payment.
     *
     * @param Booking $booking
     * @return double
     */
    public function getPaymentPrice(Booking $booking) {
        return $booking->getPaymentmethod()->getPrice();
    }

    /**
     * 1.) Germany/Company: 49,95 EUR total - 19% VAT (7,98 EUR) - (41,97 EUR netto)
     * 2.) (EU): 49,95 EUR total - 20% VAT (8,33 EUR) - (41,63 EUR netto)
     * 3.) (EU) and company: 41,63 EUR total - 0% VAT - (41,63 EUR netto)
     * 4.) (non EU)/Company: 49,95 EUR total - 0% VAT - (49,95 EUR netto)
     *
     * Pre calculate total prize, vat price, paypal prize
     * @param $country
     * @param $membership
     * @param int $vatNumber vat number
     * @param bool $isPaypal
     * @return mixed
     */
    public function calculateToSave($country, $membership, $vatNumber = null, $isPaypal = false)
    {
        // Total fee + tax(if there is one) of the membership Ex. 49.95
        $gross = round($membership->getPrice(), 2);
        // Eu Countries
        $euCountries = VATService::$validCountries;
        $isVat = false;
        // Check if has vat number for company
        // if valid vatNr then get Germany tax rate
        if (in_array($country, $euCountries)) {
            try {
                $vatNumber = preg_replace('/\s+/', '', $vatNumber);
                $this->vat->validate($vatNumber);
                $isVat = true;
                $vatCountry = "DE";
            } catch (\Exception $e) {
                $vatCountry = $country;
            }
            $countryTaxRate = $this->em->getRepository(CountryTaxRate::class)->findOneByCountryCode($vatCountry);
        } else {
            $countryTaxRate = null;
        }

        // Get netto
        $net = $this->getNet($gross, $countryTaxRate);
        // Get Vat
        $vat = $gross - $net;
        // Tax like 0.2
        $floatTax = $countryTaxRate ? $countryTaxRate->getFloatTaxRate() : 0;
        // Tax like 20.00
        $tax = $countryTaxRate ? $countryTaxRate->getTaxRate() : 0;

        // Set gross, netto, Vat, Tax Percentage
        $response['gross'] = $gross;
        $response['net'] = $net;
        $response['vat'] = $vat;
        $response['percentage'] = $tax;

        // If vat calc only gross remove vat and tax percentage
        if ($isVat) {
            $response['gross'] = $net;
            $response['vat'] = 0;
            $response['percentage'] = 0;
        }

        // If paypal add extra 2eur fee
        // @TODO Get 2eur fee from paymentMethod price
        if ($isPaypal) {
            // Add total by 2 eur
            $response['gross'] = $response['gross'] + 2;
            if (!$isVat) {
                // Calc paypal netto
                $response['paypalNet'] = (2 * 100) / (100 + $tax);
                // Calc paypal vat
                $response['paypalVat'] = 2 - $response['paypalNet'];
            } else {
                // Just add it to the netto
                $response['paypalNet'] = 2;
                // No Vat
                $response['paypalVat'] = 0;
            }
        }
        //Replace 20.00 with 20
        $response['percentage'] = preg_replace('/.00/', '', $response['percentage']);

        return $response;
    }

    /**
     * Get net based on gross and tax
     * @param float $gross
     * @param CountryTaxRate $tax
     * @return float
     */
    private function getNet($gross, $tax)
    {
        if ($tax) {
            return round($gross * 100 / (100 + floatval($tax->getTaxRate())), 2);
        }
        return round($gross, 2);
    }

}
