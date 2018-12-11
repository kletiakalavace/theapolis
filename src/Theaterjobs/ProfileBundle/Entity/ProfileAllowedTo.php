<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RevokeRights
 *
 * @ORM\Table("tj_profile_allowed_to")
 * @ORM\Entity
 */
class ProfileAllowedTo
{
    const MAX_EDUCATION_OFFER = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="comment_in_news", type="boolean", nullable=true)
     */
    private $commentInNews = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="publish_job", type="boolean", nullable=true)
     */
    private $publishJob = true;

    /**
     * @var bool
     *
     * @ORM\Column( name="email_warning", type="boolean", nullable=false)
     */
    private $emailWarning = false;

    /**
     * @var integer
     *
     * @ORM\Column( name="max_education_offer", type="integer", nullable=false, options={"default" : 3})
     */

    private  $maxEducationOffer = self::MAX_EDUCATION_OFFER;


    /** 
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="profileAllowedTo", cascade={"persist","remove"})
     **/
    private $profile;


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
     * Get commentInNews
     *
     * @return boolean
     */
    public function getCommentInNews()
    {
        return $this->commentInNews;
    }

    /**
     * Set commentInNews
     *
     * @param boolean $commentInNews
     *
     * @return RevokeRights
     */
    public function setCommentInNews($commentInNews)
    {
        $this->commentInNews = $commentInNews;

        return $this;
    }

    /**
     * Get publishJob
     *
     * @return boolean
     */
    public function getPublishJob()
    {
        return $this->publishJob;
    }

    /**
     * Set publishJob
     *
     * @param boolean $publishJob
     *
     * @return RevokeRights
     */
    public function setPublishJob($publishJob)
    {
        $this->publishJob = $publishJob;

        return $this;
    }

    /**
     * Get profile
     *
     * @return integer
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile
     *
     * @param integer $profile
     *
     * @return RevokeRights
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get emailWarning
     *
     * @return boolean
     */
    public function getEmailWarning()
    {
        return $this->emailWarning;
    }

    /**
     * Set emailWarning
     *
     * @param boolean $emailWarning
     *
     * @return ProfileAllowedTo
     */
    public function setEmailWarning($emailWarning)
    {
        $this->emailWarning = $emailWarning;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxEducationOffer()
    {
        return $this->maxEducationOffer;
    }

    /**
     * @param int $maxEducationOffer
     * @return ProfileAllowedTo
     */
    public function setMaxEducationOffer($maxEducationOffer)
    {
        $this->maxEducationOffer = $maxEducationOffer;
        return $this;
    }

}
