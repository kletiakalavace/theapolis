<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuthenticationLogs
 *
 * @ORM\Table(name="tj_user_authentication_logs")
 * @ORM\Entity
 */

class AuthenticationLogs
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
     * @var \DateTime
     *
     * @ORM\Column(name="login_date", type="datetime")
     */
    private $loginDate;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userSuccessfulLogs", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_user_user_id", referencedColumnName="id", nullable=true)
     */
    private $createdBy;


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
     * Set loginDate
     *
     * @param \DateTime $loginDate
     *
     * @return AuthenticationLogs
     */
    public function setLoginDate($loginDate)
    {
        $this->loginDate = $loginDate;

        return $this;
    }

    /**
     * Get loginDate
     *
     * @return \DateTime
     */
    public function getLoginDate()
    {
        return $this->loginDate;
    }

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     *
     * @return AuthenticationLogs
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}

