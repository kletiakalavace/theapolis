<?php

namespace Theaterjobs\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Model\AddressInterface as InserateAddress;

/**
 * Entity for the address.
 *
 * @ORM\Table(name="tj_main_addresses")
 * @ORM\Entity()
 *
 * @category Entity
 * @package  Theaterjobs\MainBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class Address implements InserateAddress {
    // @TODO Check where we use this entity
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=128, nullable=true)
     */
    protected $firstname = null;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=128, nullable=true)
     */
    protected $lastname = null;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company = null;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=128, nullable=true)
     */
    protected $street = null;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=16, nullable=true)
     */
    protected $zip = null;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=64, nullable=true)
     */
    protected $city = null;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    protected $country = null;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone = null;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=64, nullable=true)
     */
    protected $fax = null;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=128, nullable=true)
     */
    protected $url = null;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    protected $email = null;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Address
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Address
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Address
     */
    public function setCompany($company) {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return Address
     */
    public function setStreet($street) {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet() {
        return $this->street;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Address
     */
    public function setZip($zip) {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip() {
        return $this->zip;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city) {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Address
     */
    public function setCountry($country) {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Address
     */
    public function setPhone($phone) {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Address
     */
    public function setFax($fax) {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax() {
        return $this->fax;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Address
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Address
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

}
