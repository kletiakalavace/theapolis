<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JobMail.
 *
 * @ORM\Table("tj_inserate_jobmail")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\JobmailRepository")
 */
class Jobmail
{
    const STATUS_IN_QUEUE = 0;
    const STATUS_DELIVERED = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\InserateBundle\Entity\Job", inversedBy="jobmail")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     */
    private $job;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\InserateBundle\Entity\JobmailQuery", inversedBy="jobmail")
     * @ORM\JoinColumn(name="jobmail_query_id", referencedColumnName="id")
     */
    private $jobmailQuery;

    /**
     * @ORM\Column(name="status", type="smallint")
     */
    private $status = self::STATUS_IN_QUEUE;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user.
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     *
     * @return Jobmail
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set job.
     *
     * @param \Theaterjobs\InserateBundle\Entity\Job $job
     *
     * @return Jobmail
     */
    public function setJob(\Theaterjobs\InserateBundle\Entity\Job $job = null)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job.
     *
     * @return \Theaterjobs\InserateBundle\Entity\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Jobmail
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set jobmailQuery
     *
     * @param \Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery
     *
     * @return Jobmail
     */
    public function setJobmailQuery(\Theaterjobs\InserateBundle\Entity\JobmailQuery $jobmailQuery = null)
    {
        $this->jobmailQuery = $jobmailQuery;

        return $this;
    }

    /**
     * Get jobmailQuery
     *
     * @return \Theaterjobs\InserateBundle\Entity\JobmailQuery
     */
    public function getJobmailQuery()
    {
        return $this->jobmailQuery;
    }
}
