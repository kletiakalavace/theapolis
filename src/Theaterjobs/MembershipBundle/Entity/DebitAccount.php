<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Theaterjobs\MembershipBundle\Validator\Constraints as TheaterjobsAssert;
use Theaterjobs\ProfileBundle\Model\DebitAccountInterface;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;

/**
 * Entity for the debit account.
 *
 * @ORM\Table(name="tj_membership_debit_accounts")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\DebitAccountRepository")
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class DebitAccount implements DebitAccountInterface {

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
     * @ORM\OneToOne(targetEntity="Theaterjobs\MembershipBundle\Model\ProfileInterface", inversedBy="debitAccount")
     * @ORM\JoinColumn(name="tj_membership_profiles_id", referencedColumnName="id")
     * */
    private $profile;

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
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return DebitAccount
     */
    public function setIban($iban) {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string
     */
    public function getIban() {
        return $this->iban;
    }

    /**
     * Set bic
     *
     * @param string $bic
     *
     * @return DebitAccount
     */
    public function setBic($bic) {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string
     */
    public function getBic() {
        return $this->bic;
    }

    /**
     * Set accountHolder
     *
     * @param string $accountHolder
     *
     * @return DebitAccount
     */
    public function setAccountHolder($accountHolder) {
        $this->accountHolder = $accountHolder;

        return $this;
    }

    /**
     * Get accountHolder
     *
     * @return string
     */
    public function getAccountHolder() {
        return $this->accountHolder;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return DebitAccount
     */
    public function setProfile(ProfileInterface $profile = null) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * Check if its equal with other debitAccount Entity
     * @param DebitAccount $debitAccount
     * @return bool
     */
    public function isEqual($debitAccount)
    {
        if (!$debitAccount) return false;
        if ($this->iban != $debitAccount->getIban()) {
            return false;
        }
        if ($this->bic != $debitAccount->getBic()) {
           return false;
        }
        if ($this->accountHolder != $debitAccount->getAccountHolder()) {
            return false;
        }
        return true;
    }

    /**
     * takes a debitAccount entity and copies values
     * @param DebitAccount $entity
     * @return DebitAccount
     */
    public function update($entity)
    {
        $this->setAccountHolder($entity->getAccountHolder());
        $this->setBic($entity->getBic());
        $this->setIban($entity->getIban());
        return $this;
    }
}
