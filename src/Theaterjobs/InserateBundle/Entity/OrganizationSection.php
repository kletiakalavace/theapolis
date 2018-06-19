<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Entity for the organization.
 *
 * @ORM\Table(name="tj_inserate_organization_sections")
 * @ORM\Entity
 * * @Gedmo\TranslationEntity(class="OrganizationSectionTranslation")
 */
class OrganizationSection implements Translatable {
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="organizationSection")
     */
    protected $organizations;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128)
     */
    protected $slug;

    /**
     * @ORM\OneToMany(
     *   targetEntity="OrganizationSectionTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->organizations = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return OrganizationSection
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return OrganizationSection
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Add organizations
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizations
     * @return OrganizationSection
     */
    public function addOrganization(\Theaterjobs\InserateBundle\Entity\Organization $organizations)
    {
        $this->organizations[] = $organizations;

        return $this;
    }

    /**
     * Remove organizations
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organizations
     */
    public function removeOrganization(\Theaterjobs\InserateBundle\Entity\Organization $organizations)
    {
        $this->organizations->removeElement($organizations);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations() {
        return $this->translations;
    }

    /**
     * Add translations
     *
     * @param OrganizationSectionTranslation $t
     */
    public function addTranslation(OrganizationSectionTranslation $t) {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * Remove translations
     *
     * @param OrganizationSectionTranslation $translations
     */
    public function removeTranslation(OrganizationSectionTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }
}
