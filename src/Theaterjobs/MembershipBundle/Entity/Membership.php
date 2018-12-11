<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the Membership.
 *
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\MembershipRepository")
 * @ORM\Table(name="tj_membership_memberships")
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class Membership {

    const yearly = 'membership-for-one-year';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Booking", mappedBy="membership")
     */
    private $bookings;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

    /**
     * @Gedmo\Slug(
     *     fields={"title"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", length=128)
     */
    protected $slug;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="price", type="decimal", precision=13, scale=4)
     */
    private $price;

    /**
     * @ORM\Column(name="duration", type="string", options={"comment" = "ISO_8601 format"}, length=16, nullable=true)
     */
    private $duration;

//    /**
//     * @ORM\Column(name="is_subscription", options={"comment" = "autmatically extends duration"}, type="boolean", nullable=true)
//     */
//    private $isSubscription = false;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    public function setTranslatableLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->bookings = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Membership
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
     * Set description
     *
     * @param string $description
     * @return Membership
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Membership
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Membership
     */
    public function setPrice($price) {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return double
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * Set duration
     *
     * Sets the duration in ISO_8601 format
     *
     * @param string $duration
     * @return Membership
     */
    public function setDuration($duration) {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return string
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * Set isSubscription
     *
     * Sets if the Membership is a subscription and is automatically reloaded
     * At a given time a cronjobs resets membershipsExpiresAt to the new date.
     *
     * @param boolean $isSubscription
     * @return Membership
     */
//    public function setIsSubscription($isSubscription) {
//        $this->isSubscription = $isSubscription;
//
//        return $this;
//    }

    /**
     * Get isSubscription
     *
     * Gets if the Membership is a subscription and is automatically reloaded.
     * At a given time a cronjobs resets membershipsExpiresAt to the new date.
     *
     * @return boolean
     */
//    public function getIsSubscription() {
//        return $this->isSubscription;
//    }

    /**
     * Add bookings
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $bookings
     * @return Membership
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

    public function __toString() {
        return strval($this->id);
    }

}
