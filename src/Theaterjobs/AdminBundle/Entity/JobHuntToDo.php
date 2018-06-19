<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * JobHuntToDo
 *
 * @ORM\Table(name="tj_admin_jobs_hunt_todo")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\JobHuntToDoRepository")
 */
class JobHuntToDo
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
     * One jobHuntToDo has One JobHunt.
     * @ORM\OneToOne(targetEntity="JobHunt", inversedBy="jobHuntToDo")
     */
    private $jobHunt;

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
     * @return mixed
     */
    public function getJobHunt()
    {
        return $this->jobHunt;
    }

    /**
     * @param mixed $jobHunt
     * @return JobHuntToDo
     */
    public function setJobHunt($jobHunt)
    {
        $this->jobHunt = $jobHunt;
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
     * @return JobHuntToDo
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
     * @return JobHuntToDo
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}

