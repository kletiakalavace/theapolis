<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Description of EyeColor
 *
 * @author Malvin Dake
 * @author Vilson Duka
 * @ORM\Table(name="tj_profile_eyecolors")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="EyeColorTranslation")
 */
class EyeColor implements Translatable
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128)
     * @Gedmo\Translatable
     */
    protected $slug;

    /**
     * @ORM\OneToMany(
     *   targetEntity="EyeColorTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="PersonalData", mappedBy="eyeColor")
     */
    private $personalData;

    /**
     * @return mixed
     */
    public function getPersonalData()
    {
        return $this->personalData;
    }

    /**
     * @param mixed $personalData
     * @return EyeColor
     */
    public function setPersonalData($personalData)
    {
        $this->personalData = $personalData;
        return $this;
    }


    public function __construct() {
        $this->personalData = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getTranslations() {
        return $this->translations;
    }

    public function addTranslation(EyeColorTranslation $t) {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }

    public function getProfiles() {
        return $this->profiles;
    }

    public function setProfiles($profiles) {
        $this->profiles = $profiles;
    }

    /**
     * Get the name property.
     *
     * @return string
     */
    public function __toString() {
        return $this->name;
    }


    /**
     * Remove translations
     *
     * @param \Theaterjobs\ProfileBundle\Entity\EyeColorTranslation $translations
     */
    public function removeTranslation(\Theaterjobs\ProfileBundle\Entity\EyeColorTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Add profiles
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profiles
     * @return EyeColor
     */
    public function addProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profiles)
    {
        $this->profiles[] = $profiles;

        return $this;
    }

    /**
     * Remove profiles
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profiles
     */
    public function removeProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profiles)
    {
        $this->profiles->removeElement($profiles);
    }
}
