<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldEducation
 *
 * @ORM\Table("tj_profile_old_education")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\OldEducationRepository")
 */
class OldEducation
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
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="oldEducation")
     */
    private $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="education", type="text", nullable=true)
     */
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
     * Set education
     *
     * @param string $education
     *
     * @return OldEducation
     */
    public function setEducation($education)
    {
        $this->education = $education;

        return $this;
    }

    /**
     * Get education
     *
     * @return string
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return OldEducation
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
}
