<?php

namespace Theaterjobs\ProfileBundle\Model;

use Theaterjobs\MembershipBundle\Model\ProfileInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * The BillingAddressInterface
 *
 * Describes the BillingAddressInterface
 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface BillingAddressInterface {

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return BillingAddressInterface
     */
    public function setFirstname($firstname);

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return BillingAddressInterface
     */
    public function setLastname($lastname);

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set company
     *
     * @param string $company
     * @return BillingAddressInterface
     */
    public function setCompany($company);

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany();

    /**
     * Set vatId
     *
     * @param string $vatId
     * @return BillingAddressInterface
     */
    public function setVatId($vatId);

    /**
     * Get vatId
     *
     * @return string
     */
    public function getVatId();

    /**
     * Set street
     *
     * @param string $street
     * @return BillingAddressInterface
     */
    public function setStreet($street);

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Set zip
     *
     * @param string $zip
     * @return BillingAddressInterface
     */
    public function setZip($zip);

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip();

    /**
     * Set city
     *
     * @param string $city
     * @return BillingAddressInterface
     */
    public function setCity($city);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set country
     *
     * @param string $country
     * @return BillingAddressInterface
     */
    public function setCountry($country);

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();

    /**
     * Set email
     *
     * @param string $email
     * @return BillingAddressInterface
     */
    public function setEmail($email);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set profile
     *
     * @param Profile $profile
     * @return BillingAddressInterface
     */
    public function setProfile(Profile $profile);

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile();
}
