<?php

namespace Theaterjobs\StatsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Class ViewEvent
 * @package Theaterjobs\StatsBundle\Event
 */
class ViewEvent extends Event
{
    /** @var string Name class */
    private $className;

    /** @var int foreign key */
    private $fk;

    /** @var User|null user */
    private $user = null;

    /** @var boolean flag for profiles */
    private $doNotTrack = false;

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     * @return ViewEvent
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFk()
    {
        return $this->fk;
    }

    /**
     * @param mixed $fk
     * @return ViewEvent
     */
    public function setFk($fk)
    {
        $this->fk = $fk;
        return $this;
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param null|User $user
     * @return ViewEvent
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDoNotTrack()
    {
        return $this->doNotTrack;
    }

    /**
     * @param bool $doNotTrack
     * @return ViewEvent
     */
    public function setDoNotTrack($doNotTrack)
    {
        $this->doNotTrack = $doNotTrack;
        return $this;
    }
}
