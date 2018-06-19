<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * JobHunt
 *
 * @ORM\Table(name="tj_admin_jobs_hunt")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\JobHuntRepository")
 */
class JobHunt
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
     * One JobHunt has One JobHuntToDo.
     * @ORM\OneToOne(targetEntity="JobHuntToDo", inversedBy="jobHunt")
     */
    private $jobHuntToDo;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="smallint")
     */
    private $priority = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="intervalDays", type="smallint")
     */
    private $intervalDays = 30;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_checked", type="boolean")
     */
    private $isChecked = false;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

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
     * @return JobHunt
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
     * Set url
     *
     * @param string $url
     *
     * @return JobHunt
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return JobHunt
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set intervalDays
     *
     * @param integer $intervalDays
     *
     * @return JobHunt
     */
    public function setIntervalDays($intervalDays)
    {
        $this->intervalDays = $intervalDays;

        return $this;
    }

    /**
     * Get intervalDays
     *
     * @return integer
     */
    public function getIntervalDays()
    {
        return $this->intervalDays;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return JobHunt
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getisChecked()
    {
        return $this->isChecked;
    }

    /**
     * @param int $isChecked
     * @return JobHunt
     */
    public function setIsChecked($isChecked)
    {
        $this->isChecked = $isChecked;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return JobHunt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return JobHunt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobHuntToDo()
    {
        return $this->jobHuntToDo;
    }

    /**
     * @param mixed $jobHuntToDo
     * @return JobHunt
     */
    public function setJobHuntToDo($jobHuntToDo)
    {
        $this->jobHuntToDo = $jobHuntToDo;
        return $this;
    }
}

