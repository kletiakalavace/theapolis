<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EducationKind
 *
 * @ORM\Table(name="tj_inserate_educations_kind")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\EducationKindRepository")
 */
class EducationKind
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
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archivedAt", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @ORM\OneToMany(targetEntity="Education", mappedBy="educationKind")
     **/
    private $education;


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
     *
     * @return EducationKind
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EducationKind
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     *
     * @return EducationKind
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * Set education
     *
     * @param integer $education
     *
     * @return EducationKind
     */
    public function setEducation($education)
    {
        $this->education = $education;

        return $this;
    }

    /**
     * Get education
     *
     * @return integer
     */
    public function getEducation()
    {
        return $this->education;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->education = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add education
     *
     * @param \Theaterjobs\InserateBundle\Entity\Education $education
     *
     * @return EducationKind
     */
    public function addEducation(\Theaterjobs\InserateBundle\Entity\Education $education)
    {
        $this->education[] = $education;

        return $this;
    }

    /**
     * Remove education
     *
     * @param \Theaterjobs\InserateBundle\Entity\Education $education
     */
    public function removeEducation(\Theaterjobs\InserateBundle\Entity\Education $education)
    {
        $this->education->removeElement($education);
    }
}
