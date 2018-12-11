<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Description of EmailChangeRequest
 * @ORM\Table(name="tj_user_email_change_request")
 * @ORM\Entity()
 */
class EmailChangeRequest {
    
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
     *
     * @ORM\Column(name="old_email", type="string", length=128)
     */
    private $oldMail;
    
    /**
     * @var string
     *
     * @ORM\Column(name="new_email", type="string", length=128)
     */
    private $newMail;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="requested_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $requestedAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $confirmationToken;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;
    

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
     * Set oldMail
     *
     * @param string $oldMail
     *
     * @return EmailChangeRequest
     */
    public function setOldMail($oldMail)
    {
        $this->oldMail = $oldMail;

        return $this;
    }

    /**
     * Get oldMail
     *
     * @return string
     */
    public function getOldMail()
    {
        return $this->oldMail;
    }

    /**
     * Set newMail
     *
     * @param string $newMail
     *
     * @return EmailChangeRequest
     */
    public function setNewMail($newMail)
    {
        $this->newMail = $newMail;

        return $this;
    }

    /**
     * Get newMail
     *
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }

    /**
     * Set requestedAt
     *
     * @param \DateTime $requestedAt
     *
     * @return EmailChangeRequest
     */
    public function setRequestedAt($requestedAt)
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    /**
     * Get requestedAt
     *
     * @return \DateTime
     */
    public function getRequestedAt()
    {
        return $this->requestedAt;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     *
     * @return EmailChangeRequest
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return EmailChangeRequest
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Check if email confirmation token has expired
     *
     * @param $ttl integer
     * @return boolean
     */
    public function isChangeEmailRequestExpired($ttl)
    {
        return $this->getRequestedAt() instanceof \DateTime &&
            $this->getRequestedAt()->getTimestamp() + $ttl < time();
    }
}
