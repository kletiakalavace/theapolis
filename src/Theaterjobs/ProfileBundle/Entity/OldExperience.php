<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldExperience
 *
 * @ORM\Table("tj_profile_old_experience")
 * @ORM\Entity()
 */
class OldExperience
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
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="oldExperience")
     */
    private $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="text", nullable=true)
     */
    private $experience;


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
     * Set experience
     *
     * @param string $experience
     *
     * @return OldExperience
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return string
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return OldExperience
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
