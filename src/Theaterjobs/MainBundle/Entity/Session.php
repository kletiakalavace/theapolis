<?php

namespace Theaterjobs\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for Session.
 *
 * @ORM\Table(name="tj_main_session")
 * @ORM\Entity
 */
class Session {
    // TODO Check if we use this anymore Jurgen, IGLI, JANA
    /**
     * @ORM\Column(name="session_id", type="string", length=255)
     * @ORM\Id
     */
    protected $session_id;

    /**
     * @ORM\Column(name="session_value", type="text")
     */
    protected $session_value;

    /**
     * @ORM\Column(name="session_time", type="integer")
     */
    protected $session_time;

    /**
     * @ORM\Column(name="session_data", type="string", length=400)
     */
    protected $session_data;


    /**
     * Set session_id
     *
     * @param string $sessionId
     * @return Session
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;

        return $this;
    }

    /**
     * Get session_id
     *
     * @return string 
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set session_value
     *
     * @param string $sessionValue
     * @return Session
     */
    public function setSessionValue($sessionValue)
    {
        $this->session_value = $sessionValue;

        return $this;
    }

    /**
     * Get session_value
     *
     * @return string 
     */
    public function getSessionValue()
    {
        return $this->session_value;
    }

    /**
     * Set session_time
     *
     * @param integer $sessionTime
     * @return Session
     */
    public function setSessionTime($sessionTime)
    {
        $this->session_time = $sessionTime;

        return $this;
    }

    /**
     * Get session_time
     *
     * @return integer 
     */
    public function getSessionTime()
    {
        return $this->session_time;
    }

    /**
     * Set session_data
     *
     * @param string $sessionData
     * @return Session
     */
    public function setSessionData($sessionData)
    {
        $this->session_data = $sessionData;

        return $this;
    }

    /**
     * Get session_data
     *
     * @return string 
     */
    public function getSessionData()
    {
        return $this->session_data;
    }
}
