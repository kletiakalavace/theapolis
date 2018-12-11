<?php

namespace Theaterjobs\ProfileBundle\Model;

/**
 * The AddressInterface
 *
 * Describes the AddressInterface

 *
 * @category Model
 * @package  Theaterjobs\ProfileBundle\Model
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface AddressInterface {

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set street
     *
     * @param string $street
     * @return InserateAddressInterface
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
     * @return InserateAddressInterface
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
     * @return InserateAddressInterface
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
     * @return InserateAddressInterface
     */
    public function setCountry($country);

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();
}
