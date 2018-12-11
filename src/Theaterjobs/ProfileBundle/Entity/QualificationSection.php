<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * Description of GeneralSection
 *
 * @ORM\Table(name="tj_profile_section_qualification")
 * @ORM\Entity(repositoryClass="QualificationSectionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class QualificationSection
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne( targetEntity="Profile", mappedBy="qualificationSection" )
     */
    protected $profile;

    /**
     * @ORM\OneToMany(targetEntity="Qualification", mappedBy="qualificationSection", cascade={"persist","remove"})
     * @OrderBy({"startDate" = "DESC"})
     */
    private $qualifications;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->qualifications = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     * @return QualificationSection
     */
    public function setProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Add qualifications
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Qualification $qualifications
     * @return QualificationSection
     */
    public function addQualification(\Theaterjobs\ProfileBundle\Entity\Qualification $qualifications)
    {
        $qualifications->setQualificationSection($this);
        $this->qualifications[] = $qualifications;

        return $this;
    }

    /**
     * Remove qualifications
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Qualification $qualifications
     */
    public function removeQualification(\Theaterjobs\ProfileBundle\Entity\Qualification $qualifications)
    {
        $this->qualifications->removeElement($qualifications);
    }

    /**
     * Get qualifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQualifications()
    {
        return $this->qualifications;
    }

}
