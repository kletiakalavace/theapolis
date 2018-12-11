<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Theaterjobs\MembershipBundle\Validator\Constraints as TheaterjobsAssert;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * Billing
 *
 * @ORM\Table(name="tj_membership_billingaddress")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\BillingAddressRepository")
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class BillingAddress {

    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="\Theaterjobs\MembershipBundle\Model\ProfileInterface", inversedBy="billingAddress")
     * @ORM\JoinColumn(name="tj_membership_profiles_id", referencedColumnName="id", nullable=false)
     */
    protected $profile;

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
     * @TheaterjobsAssert\NoCompanyWithVatId(message="form.error.billing_address.company.empty")
     */
    protected $company = null;

    /**
     * @var string
     *
     * @ORM\Column(name="vat_id", type="string", length=255, nullable=true)
     * @todo check this function for validation, do we need them? ValidNumber()
     */
    protected $vatId = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="vat_id_is_validated", type="boolean")
     */
    protected $vatIdIsValidated = true;

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
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    protected $country = null;

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
     * @return BillingAddress
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
     * @return BillingAddress
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
     * @return BillingAddress
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
     * Set vatId
     *
     * @param string $vatId
     * @return BillingAddress
     */
    public function setVatId($vatId) {
        $this->vatId = $vatId;

        return $this;
    }

    /**
     * Get vatId
     *
     * @return string
     */
    public function getVatId() {
        return $this->vatId;
    }

    /**
     * Set vatIdIsValidated
     *
     * @param boolean $vatIdIsValidated
     * @return BillingAddress
     */
    public function setVatIdIsValidated($vatIdIsValidated) {
        $this->vatIdIsValidated = $vatIdIsValidated;

        return $this;
    }

    /**
     * Get vatIdIsValidated
     *
     * @return boolean
     */
    public function getVatIdIsValidated() {
        return $this->vatIdIsValidated;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return BillingAddress
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
     * @return BillingAddress
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
     * @return BillingAddress
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
     * @return BillingAddress
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
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return BillingAddress
     */
    public function setProfile(ProfileInterface $profile) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * Serialize billing Address object
     */
    public function serialize()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizer = new ObjectNormalizer();
        $normalizer->setIgnoredAttributes(['profile']);
        $normalizers = [$normalizer];
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($this,  "json");
    }

}
