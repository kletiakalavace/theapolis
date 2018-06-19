<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_profile_eyeColor_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tj_profile_eyeColor_translations_unique_idx", columns={
 *         "locale", "tj_profile_eyeColor_id", "field"
 *     })}
 * )
 */
class EyeColorTranslation extends AbstractPersonalTranslation {

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
     * @ORM\ManyToOne(targetEntity="EyeColor", inversedBy="translations")
     * @ORM\JoinColumn(name="tj_profile_eyeColor_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

}
