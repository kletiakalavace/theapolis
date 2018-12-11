<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Model\BookingInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Booking
 *
 * @ORM\Table(name="tj_membership_bookings")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\BookingRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class Booking implements BookingInterface
{

    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\MembershipBundle\Model\ProfileInterface", inversedBy="bookings", cascade={"persist"})
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    private $profile;

    /**
     * @ORM\ManyToOne(targetEntity="Paymentmethod", inversedBy="bookings", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(
     *  name="tj_membership_paymentmethods_id", referencedColumnName="id"
     * )
     */
    private $paymentmethod;

    /**
     * @ORM\ManyToOne(targetEntity="Membership", inversedBy="bookings")
     * @ORM\JoinColumn(name="tj_membership_memberships_id", referencedColumnName="id")
     */
    private $membership;

    /**
     * @ORM\OneToMany(targetEntity="Billing", mappedBy="booking")
     */
    private $billings;

    public function __construct()
    {
        $this->billings = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set paymentmethod
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Paymentmethod $paymentmethod
     * @return Booking
     */
    public function setPaymentmethod(\Theaterjobs\MembershipBundle\Entity\Paymentmethod $paymentmethod = null)
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

    /**
     * Get paymentmethod
     *
     * @return \Theaterjobs\MembershipBundle\Entity\Paymentmethod
     */
    public function getPaymentmethod()
    {
        return $this->paymentmethod;
    }

    /**
     * Set membership
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Membership $membership
     * @return Booking
     */
    public function setMembership(\Theaterjobs\MembershipBundle\Entity\Membership $membership = null)
    {
        $this->membership = $membership;

        return $this;
    }

    /**
     * Get membership
     *
     * @return \Theaterjobs\MembershipBundle\Entity\Membership
     */
    public function getMembership()
    {
        return $this->membership;
    }

    /**
     * Add Billing
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Billing $billing
     *
     * @return Booking
     */
    public function addBilling(\Theaterjobs\MembershipBundle\Entity\Billing $billing)
    {
        $this->billings[] = $billing;

        return $this;
    }

    /**
     * Remove billing
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Billing $billing
     */
    public function removeBilling(\Theaterjobs\MembershipBundle\Entity\Billing $billing)
    {
        $this->billings->removeElement($billing);
    }

    /**
     * Get Billings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillings()
    {
        return $this->billings;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\MembershipBundle\Model\ProfileInterface $profile
     * @return Booking
     */
    public function setProfile(ProfileInterface $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Get last billing of booking
     * @return \Theaterjobs\MembershipBundle\Entity\Billing
     */
    public function getLastBilling()
    {
        // Collect an array iterator.
        $iterator = $this->billings->getIterator();

        // Do sort the new iterator.
        $iterator->uasort(function ($a, $b) {
            return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->billings = new ArrayCollection(iterator_to_array($iterator));

        return $this->billings->first();
    }

}
