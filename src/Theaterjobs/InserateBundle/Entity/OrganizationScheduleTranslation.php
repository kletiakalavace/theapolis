<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="tj_inserate_organization_schedule_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tj_inserate_organization_schedule_translations_unique_idx", columns={
 *         "locale", "tj_inserate_organization_schedule_id", "field"
 *     })}
 * )
 */
class OrganizationScheduleTranslation extends AbstractPersonalTranslation {

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
     * @ORM\ManyToOne(targetEntity="OrganizationSchedule", inversedBy="translations")
     * @ORM\JoinColumn(name="tj_inserate_organization_schedule_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

}
