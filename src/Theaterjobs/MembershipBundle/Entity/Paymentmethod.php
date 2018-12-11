<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use \Doctrine\Common\Collections\ArrayCollection;
use Theaterjobs\ProfileBundle\Model\PaymentmethodInterface as ProfilePaymentmethod;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;
use Gedmo\Translatable\Translatable;

/**
 * Paymentmethod
 *
 * @ORM\Table(name="tj_membership_paymentmethods")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\PaymentmethodRepository")
 * @category Entity
 * @Gedmo\TranslationEntity(class="PaymentmethodTranslation")
 */
class Paymentmethod implements ProfilePaymentmethod, Translatable
{

    const DIRECT_DEBIT = 'direct';
    const PREPAYMENT = 'prepay';
    const PAYPAL = 'paypal';
    const SOFORT = 'sofort';
    const IMMEDIATELY_TRANSFER = 'immediate';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\MembershipBundle\Model\ProfileInterface", inversedBy="blockedPaymentmethods"), fetch="EAGER")
     * @ORM\JoinTable(name="tj_membership_paymentmethods_blocked_profiles",
     *      joinColumns={@ORM\JoinColumn(name="tj_profile_paymentmethods_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tj_profile_profiles_id", referencedColumnName="id")}
     *      )
     */
    private $blockedForProfiles;

    /**
     * @ORM\OneToMany(
     *   targetEntity="PaymentmethodTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
    * @ORM\Column(
     *     name="is_subscription",
     *     options={"comment" = "autmatically extends duration"},
     *     type="boolean", nullable=true)
    */
    private $isSubscription = false;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=32, unique=true)
     */
    private $title;

    /** @ORM\Column(name="short", type="string", length=16, unique=true) */
    private $short;

    /**
     * Extra fees that may apply to the payment method Ex. Paypal price is 2.00 Eur
     * @ORM\Column(name="price", type="decimal", precision=13, scale=4)
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="Booking", mappedBy="paymentmethod")
     */
    private $bookings;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive = true;

    /**
     * Constructor
     */
    public function __construct() {
        $this->bookings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blockedForProfiles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->id);
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Paymentmethod
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set short
     *
     * @param string $short
     * @return Paymentmethod
     */
    public function setShort($short) {
        $this->short = $short;

        return $this;
    }

    /**
     * Get short
     *
     * @return string
     */
    public function getShort() {
        return $this->short;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Paymentmethod
     */
    public function setPrice($price) {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * Add bookings
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $bookings
     * @return Paymentmethod
     */
    public function addBooking(\Theaterjobs\MembershipBundle\Entity\Booking $bookings) {
        $this->bookings[] = $bookings;

        return $this;
    }

    /**
     * Remove bookings
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $bookings
     */
    public function removeBooking(\Theaterjobs\MembershipBundle\Entity\Booking $bookings) {
        $this->bookings->removeElement($bookings);
    }

    /**
     * Get bookings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBookings() {
        return $this->bookings;
    }

    /**
     * Add blockedForProfiles
     *
     * @param ProfileInterface $blockedForProfiles
     * @return Paymentmethod
     */
    public function addBlockedForProfile(ProfileInterface $blockedForProfiles) {
        $this->blockedForProfiles[] = $blockedForProfiles;

        return $this;
    }

    /**
     * Remove blockedForProfiles
     *
     * @param ProfileInterface $blockedForProfiles
     */
    public function removeBlockedForProfile(ProfileInterface $blockedForProfiles) {
        $this->blockedForProfiles->removeElement($blockedForProfiles);
    }

    /**
     * Get blockedForProfiles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlockedForProfiles() {
        return $this->blockedForProfiles;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Paymentmethod
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive() {
        return $this->isActive;
    }

    public function getTranslations() {
        return $this->translations;
    }

    public function addTranslation(PaymentmethodTranslation $t) {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * Remove translations
     *
     * @param \Theaterjobs\MembershipBundle\Entity\PaymentmethodTranslation $translations
     */
    public function removeTranslation(\Theaterjobs\MembershipBundle\Entity\PaymentmethodTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * @return mixed
     */
    public function getIsSubscription()
    {
        return $this->isSubscription;
    }

    /**
     * @param mixed $isSubscription
     * @return Paymentmethod
     */
    public function setIsSubscription($isSubscription)
    {
        $this->isSubscription = $isSubscription;
        return $this;
    }


    /**
     * Check if payment method is Direct Debit
     */
    public function isDebit()
    {
        return $this->getShort() == self::DIRECT_DEBIT;
    }

    /**
     * Check if payment method is Sofort
     */
    public function isSofort()
    {
        return $this->getShort() == self::SOFORT;
    }

    /**
     * Check if payment method is Paypal
     */
    public function isPaypal()
    {
        return $this->getShort() == self::PAYPAL;
    }

    /**
     * Check if payment method is Paypal or Sofort
     */
    public function isPaypalorSofort()
    {
        return $this->isPaypal() || $this->isSofort();
    }
}
