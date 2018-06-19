<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\ProfileBundle\Model\JobInterface;

/**
 * ApplicationTrack
 *
 * @ORM\Table(name="tj_inserate_job_application_track")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\ApplicationTrackRepository")
 */
class ApplicationTrack
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
     * @ORM\Column(name="email", type="string")
     */
    private $email;
    
    /**
     * @var string
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\Inserate", inversedBy="applicationRequests")
     */
    private $job;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="applicationRequests")
     */
    private $profile;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ApplicationTrack
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return ApplicationTrack
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set job
     *
     * @param JobInterface $job
     *
     * @return ApplicationTrack
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return integer
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ApplicationTrack
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return integer
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     * @return ApplicationTrack
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }


}


