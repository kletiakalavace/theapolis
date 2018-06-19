<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Entity for the gratification.
 *
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\GratificationRepository")
 * @ORM\Table(name="tj_inserate_gratifications")
 * * @Gedmo\TranslationEntity(class="GratificationTranslation")
 *
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
class Gratification implements Translatable{

    const TYPE_JOB = 'job';
    const TYPE_EDU = 'edu';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Inserate", mappedBy="gratification")
     */
    protected $inserates;

    /**
     * @ORM\ManyToMany(targetEntity="JobmailQuery", mappedBy="gratification")
     */
    protected $jobmailQuery;

    /**
     * @ORM\Column(name="type_of", type="string", length=16)
     */
    protected $typeOf;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255)
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
     *   targetEntity="GratificationTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;


    /**
     * Constructor
     */
    public function __construct() {
        $this->inserates = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set typeOf
     *
     * @param string $typeOf
     * @return Gratification
     */
    public function setTypeOf($typeOf) {
        if (!in_array($typeOf, array(self::TYPE_JOB, self::TYPE_EDU))) {
            throw new \InvalidArgumentException('Invalid type');
        }
        $this->typeOf = $typeOf;

        return $this;
    }

    /**
     * Get typeOf
     *
     * @return string
     */
    public function getTypeOf() {
        return $this->typeOf;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Gratification
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
     * Set slug
     *
     * @param string $slug
     * @return Gratification
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Add inserates
     *
     * @param \Theaterjobs\InserateBundle\Entity\Inserate $inserates
     * @return Gratification
     */
    public function addInserate(\Theaterjobs\InserateBundle\Entity\Inserate $inserates) {
        $this->inserates[] = $inserates;

        return $this;
    }

    /**
     * Remove inserates
     *
     * @param \Theaterjobs\InserateBundle\Entity\Inserate $inserates
     */
    public function removeInserate(\Theaterjobs\InserateBundle\Entity\Inserate $inserates) {
        $this->inserates->removeElement($inserates);
    }

    /**
     * Get inserates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInserates() {
        return $this->inserates;
    }


    /**
     * Add jobmailQuery
     *
     * @param \Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery
     *
     * @return Gratification
     */
    public function addJobmailQuery(\Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery)
    {
        $this->jobmailQuery[] = $jobmailQuery;

        return $this;
    }

    /**
     * Remove jobmailQuery
     *
     * @param \Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery
     */
    public function removeJobmailQuery(\Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery)
    {
        $this->jobmailQuery->removeElement($jobmailQuery);
    }

    /**
     * Get jobmailQuery
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobmailQuery()
    {
        return $this->jobmailQuery;
    }

    public function getTranslations() {
        return $this->translations;
    }

    public function addTranslation(GratificationTranslation $t) {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * Remove translations
     *
     * @param \Theaterjobs\InserateBundle\Entity\GratificationTranslation $translations
     */
    public function removeTranslation(\Theaterjobs\InserateBundle\Entity\GratificationTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * @return bool
     */
    public function isEduType()
    {
        return $this->getTypeOf() === self::TYPE_EDU;
    }

    /**
     * @return bool
     */
    public function isJobType()
    {
        return $this->getTypeOf() === self::TYPE_JOB;
    }
}
