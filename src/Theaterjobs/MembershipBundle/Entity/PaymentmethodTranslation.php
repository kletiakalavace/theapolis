<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_membership_paymentmethods_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tj_membership_paymentmethods_translations_unique_idx", columns={
 *         "locale", "tj_membership_paymentmethods_id", "field"
 *     })}
 * )
 */
class PaymentmethodTranslation extends AbstractPersonalTranslation {

    /**
     * Convenient constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value) {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    /**
     * @ORM\ManyToOne(targetEntity="Paymentmethod", inversedBy="translations")
     * @ORM\JoinColumn(name="tj_membership_paymentmethods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

}
