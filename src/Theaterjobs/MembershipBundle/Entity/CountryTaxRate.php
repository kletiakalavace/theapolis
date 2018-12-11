<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CountryPercentageTaxRate
 *
 * @ORM\Table(
 *    name="tj_membership_country_tax_rates",
 *    uniqueConstraints={
 *       @ORM\UniqueConstraint(
 *          name="country_idx", columns={"country_code"}
 *       )
 *    }
 * )
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\CountryTaxRateRepository")
 */
class CountryTaxRate
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=2)
     */
    private $countryCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_rate", type="decimal", precision=4, scale=2)
     */
    private $taxRate;


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
     * Set countryCode
     *
     * @param string $countryCode
     * @return CountryTaxRate
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set taxRate
     *
     * @param string $taxRate
     * @return CountryTaxRate
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
     * Get tax rate with decimal like 0.19 instead of 19
     * @return float
     */
    public function getFloatTaxRate()
    {
        return floatval('0.' . $this->taxRate);
    }
}
