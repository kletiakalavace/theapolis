<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldExtras
 *
 * @ORM\Table("tj_profile_old_extras")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\OldExtrasRepository")
 */
class OldExtras
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
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="oldExtras")
     */
    private $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="extras", type="text", nullable=true)
     */
    private $extras;

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
     * Set extras
     *
     * @param string $extras
     *
     * @return OldExtras
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * Get extras
     *
     * @return string
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return OldExtras
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
