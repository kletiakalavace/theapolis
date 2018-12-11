<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * Iban Blacklist
 *
 * @ORM\Table(name="tj_membership_iban_blacklist")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\IbanBlacklistRepository")
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 */
class IbanBlacklist {
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
     * @var string
     * @Encrypted
     * @ORM\Column(name="iban", type="string", length=255, nullable=false)
     */
    protected $iban = null;

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
     * Set iban
     *
     * @param string $iban
     *
     * @return IbanBlacklist
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
}
