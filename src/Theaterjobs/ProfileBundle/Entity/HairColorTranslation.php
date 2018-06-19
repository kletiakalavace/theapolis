<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_profile_hairColor_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tj_profile_hairColor_translations_unique_idx", columns={
 *         "locale", "tj_profile_hairColor_id", "field"
 *     })}
 * )
 */
class HairColorTranslation extends AbstractPersonalTranslation {

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
     * @ORM\ManyToOne(targetEntity="HairColor", inversedBy="translations")
     * @ORM\JoinColumn(name="tj_profile_hairColor_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

}
