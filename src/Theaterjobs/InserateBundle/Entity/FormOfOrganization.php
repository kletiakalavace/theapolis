<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Entity for the form of organization.
 *
 * @ORM\Entity
 * @ORM\Table(name="tj_inserate_form_of_organizations")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="FormOfOrganizationTranslation")
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class FormOfOrganization implements Translatable {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Organization", mappedBy="form")
     */
    protected $organizations;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", unique=true, nullable=false)
     */
    protected $name;

    
    /**
     * @ORM\OneToMany(
     *   targetEntity="FormOfOrganizationTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct() {
        $this->organizations = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
     * @return FormOfOrganization
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
     * Add organizations
     *
     * @param Organization $organizations
     * @return FormOfOrganization
     */
    public function addOrganization(Organization $organizations) {
        $this->organizations[] = $organizations;

        return $this;
    }

    /**
     * Remove organizations
     *
     * @param Organization $organizations
     */
    public function removeOrganization(Organization $organizations) {
        $this->organizations->removeElement($organizations);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizations() {
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
     * @param FormOfOrganizationTranslation $t
     */
    public function addTranslation(FormOfOrganizationTranslation $t) {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }
    
    /**
     * Remove translations
     *
     * @param FormOfOrganizationTranslation $translations
     */
    public function removeTranslation(FormOfOrganizationTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

}
