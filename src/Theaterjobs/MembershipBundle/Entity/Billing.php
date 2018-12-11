<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * Billing
 *
 * @ORM\Table(name="tj_membership_billings")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\BillingRepository")
 * @ORM\EntityListeners({"\Theaterjobs\MembershipBundle\EventListener\BillingListener"})
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class Billing
{

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
     * @ORM\ManyToOne(targetEntity="BillingStatus", inversedBy="billings")
     * @ORM\JoinColumn(
     *  name="tj_membership_billingstati_id", referencedColumnName="id", onDelete="CASCADE"
     * )
     */
    protected $billingStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=32, nullable=true)
     */
    protected $number = null;


    /**
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="billings", cascade={"all"})
     * @ORM\JoinColumn(
     *  name="tj_membership_booking_id", referencedColumnName="id"
     * )
     */
    protected $booking;

    /**
     * @var float
     *
     * @ORM\Column(name="paymentmethod_price", type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $paymentmethodPrice = null;

    /**
     * @var float
     *
     * @ORM\Column(name="sum_net", type="decimal", precision=13, scale=2, nullable=false)
     */
    protected $sumNet = null;

    /**
     * @var float
     *
     * @ORM\Column(name="sum_gross", type="decimal", precision=13, scale=2, nullable=false)
     */
    protected $sumGross = null;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_rate", type="decimal", precision=4, scale=2, nullable=true)
     */
    protected $taxRate = null;

    /**
     * @var float
     *
     * @ORM\Column(name="sum_vat", type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $sumVat = null;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="decimal", precision=13, scale=2, nullable=false)
     */
    protected $total = null;

    /**
     * @var string
     * @Encrypted
     * @ORM\Column(name="iban", type="string", length=255 , nullable=true)
     */
    private $iban;

    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=255 , nullable=true)
     */
    private $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="account_holder", type="string", length=255 , nullable=true)
     */
    private $accountHolder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\MembershipBundle\Entity\SepaMandate", cascade={"persist"})
     * @JoinColumn(name="sepa_id", referencedColumnName="id")
     *
     */
    protected $sepa;

    /**
     * @var string
     *
     * @ORM\Column(name="sepa_downloaded_by_admin", type="boolean")
     */
    private $downloadedSepa = false;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="string", length=15, nullable=true)
     */
    private $sequence = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="expire_email", type="boolean", nullable=true)
     */
    private $expireEmail = false;

    /**
     * @var string
     *
     * @ORM\Column(name="billingAddress", type="json_array", nullable=true)
     */
    private $billingAddress = null;

    /**
     * @var \DateTime
     * @ORM\Column(name="time_period_start", type="datetime", nullable=true)
     */
    protected $timePeriodStart;

    /**
     * @ORM\Column(name="time_period_end", type="datetime", nullable=true)
     */
    protected $timePeriodEnd;

    /**
     * @var bool
     * @ORM\Column(name="is_old", type="boolean")
     */
    protected $isOld = false;

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
     * Set number
     *
     * @param string $number
     * @return Billing
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set paymentmethodPrice
     *
     * @param string $paymentmethodPrice
     * @return Billing
     */
    public function setPaymentmethodPrice($paymentmethodPrice)
    {
        $this->paymentmethodPrice = $paymentmethodPrice;

        return $this;
    }

    /**
     * Get paymentmethodPrice
     *
     * @return string
     */
    public function getPaymentmethodPrice()
    {
        return $this->paymentmethodPrice;
    }

    /**
     * Set sumNet
     *
     * @param string $sumNet
     * @return Billing
     */
    public function setSumNet($sumNet)
    {
        $this->sumNet = $sumNet;

        return $this;
    }

    /**
     * Get sumNet
     *
     * @return string
     */
    public function getSumNet()
    {
        return $this->sumNet;
    }

    /**
     * Set sumGross
     *
     * @param string $sumGross
     * @return Billing
     */
    public function setSumGross($sumGross)
    {
        $this->sumGross = $sumGross;

        return $this;
    }

    /**
     * Get sumGross
     *
     * @return string
     */
    public function getSumGross()
    {
        return $this->sumGross;
    }

    /**
     * Set taxRate
     *
     * @param string $taxRate
     * @return Billing
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get taxRate
     *
     * @return string
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Set sumVat
     *
     * @param string $sumVat
     * @return Billing
     */
    public function setSumVat($sumVat)
    {
        $this->sumVat = $sumVat;

        return $this;
    }

    /**
     * Get sumVat
     *
     * @return string
     */
    public function getSumVat()
    {
        return $this->sumVat;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return Billing
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set billing status
     *
     * @param BillingStatus|null $billingStatus
     * @return Billing
     */
    public function setBillingStatus(BillingStatus $billingStatus = null)
    {
        $this->billingStatus = $billingStatus;

        return $this;
    }

    /**
     * Get billing status
     *
     * @return BillingStatus
     */
    public function getBillingStatus()
    {
        return $this->billingStatus;
    }

    /**
     * Set booking
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $booking
     * @return Billing
     */
    public function setBooking(\Theaterjobs\MembershipBundle\Entity\Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \Theaterjobs\MembershipBundle\Entity\Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return Billing
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set bic
     *
     * @param string $bic
     *
     * @return Billing
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set accountHolder
     *
     * @param string $accountHolder
     *
     * @return Billing
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;

        return $this;
    }

    /**
     * Get accountHolder
     *
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Billing
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * Set downloadedSepa
     *
     * @param boolean $downloadedSepa
     *
     * @return Billing
     */
    public function setDownloadedSepa($downloadedSepa)
    {
        $this->downloadedSepa = $downloadedSepa;

        return $this;
    }

    /**
     * Get downloadedSepa
     *
     * @return boolean
     */
    public function getDownloadedSepa()
    {
        return $this->downloadedSepa;
    }

    /**
     * Set sequence
     *
     * @param string $sequence
     *
     * @return Billing
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }


    /**
     * @return bool
     */
    public function getExpireEmail()
    {
        return $this->expireEmail;
    }

    /**
     * @param $expireEmail
     */
    public function setExpireEmail($expireEmail)
    {
        $this->expireEmail = $expireEmail;
    }

    /**
     * Check if billing status is Completed
     */
    public function isCompleted()
    {
        return $this->getBillingStatus()->getName() == BillingStatus::COMPLETE;
    }

    /**
     * Check if billing status is Pending
     */
    public function isPending()
    {
        return $this->getBillingStatus()->getName() == BillingStatus::PENDING;
    }

    /**
     * Check if billing status is Storno
     */
    public function isStorno()
    {
        return $this->getBillingStatus()->getName() == BillingStatus::STORNO;
    }

    /**
     * Check if billing status is Open
     */
    public function isOpen()
    {
        return $this->getBillingStatus()->getName() == BillingStatus::OPEN;
    }

    /**
     * @return array
     */
    public function getBillingAddress()
    {
        return $this->billingAddress ? json_decode($this->billingAddress) : null;
    }

    /**
     * @param string $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return SepaMandate | null
     */
    public function getSepa()
    {
        return $this->sepa;
    }

    /**
     * @param mixed $sepa
     * @return Billing
     */
    public function setSepa($sepa)
    {
        $this->sepa = $sepa;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimePeriodStart()
    {
        return $this->timePeriodStart;
    }

    /**
     * @param \DateTime $timePeriodStart
     */
    public function setTimePeriodStart($timePeriodStart)
    {
        $this->timePeriodStart = $timePeriodStart;
    }

    /**
     * @return mixed
     */
    public function getTimePeriodEnd()
    {
        return $this->timePeriodEnd;
    }

    /**
     * @param mixed $timePeriodEnd
     */
    public function setTimePeriodEnd($timePeriodEnd)
    {
        $this->timePeriodEnd = $timePeriodEnd;
    }

    /**
     * @return bool
     */
    public function isOld()
    {
        return $this->isOld;
    }

    /**
     * @param bool $isOld
     */
    public function setIsOld($isOld)
    {
        $this->isOld = $isOld;
    }

    /**
     * Get file name of the bill
     * @param $isSepa
     * @return string
     */
    public function getFileName($isSepa)
    {
        if ($isSepa) {
            return $this->getSepa()->getMandateReference();
        }
        return "THEAPOLIS-Rechnungsnr-" . $this->getNumber();
    }
}
