<?php

namespace Theaterjobs\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * Class NotificationEvent
 * @package Theaterjobs\UserBundle\Event
 */
class NotificationEvent extends Event
{
    protected $objectClass;

    protected $objectId;

    protected $notification;

    protected $users;

    protected $from = null;

    protected $type = null;

    protected $flush = true;

    /**
     * @return mixed
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * @param mixed $objectClass
     * @return NotificationEvent
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param mixed $objectId
     * @return NotificationEvent
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     * @return NotificationEvent
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     * @return NotificationEvent
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     * @return NotificationEvent
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return NotificationEvent
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlush()
    {
        return $this->flush;
    }

    /**
     * @param mixed $flush
     * @return NotificationEvent
     */
    public function setFlush($flush)
    {
        $this->flush = $flush;
        return $this;
    }

 }
