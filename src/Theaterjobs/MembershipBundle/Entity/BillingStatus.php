<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BillingStatus
 *
 * @ORM\Table(name="tj_membership_billingstati")
 * @ORM\Entity
 */
class BillingStatus
{

    const OPEN = 'open';
    const PENDING = 'pending';
    const COMPLETE = 'complete';
    const STORNO = 'storno';

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
     * @ORM\Column(name="name", type="string", length=16)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Billing", mappedBy="billingStatus")
     */
    protected $billings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->billings = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return BillingStatus
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Add billings
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Billing $billings
     * @return BillingStatus
     */
    public function addBilling(\Theaterjobs\MembershipBundle\Entity\Billing $billings) {
        $this->billings[] = $billings;

        return $this;
    }

    /**
     * Remove billings
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Billing $billings
     */
    public function removeBilling(\Theaterjobs\MembershipBundle\Entity\Billing $billings) {
        $this->billings->removeElement($billings);
    }

    /**
     * Get billings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillings() {
        return $this->billings;
    }

    /**
     * Check if billingStatus is open
     * @return bool
     */
    public function isOpen()
    {
        return $this->name == self::OPEN;
    }

    /**
     * Check if billingStatus is pending
     * @return bool
     */
    public function isPending()
    {
        return $this->name == self::PENDING;
    }

    /**
     * Check if billingStatus is complete
     * @return bool
     */
    public function isComplete()
    {
        return $this->name == self::COMPLETE;
    }

    /**
     * Check if billingStatus is storno
     * @return bool
     */
    public function isStorno()
    {
        return $this->name == self::STORNO;
    }

}
