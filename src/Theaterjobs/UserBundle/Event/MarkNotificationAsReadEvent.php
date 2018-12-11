<?php

namespace Theaterjobs\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MarkNotificationAsReadEvent extends Event {

    protected $object;
        
    protected $user;
    
    protected $from;
        
    protected $notificationCode;

    protected $flush;

    public function __construct($object, $notificationCode = null, $user = null, $from = null, $flush = true) {
        $this->object = $object;
        $this->from = $from;
        $this->user = $user;
        $this->notificationCode = $notificationCode;
        $this->flush = $flush;
    }
    
    function getObject() {
        return $this->object;
    }

    function getFrom() {
        return $this->from;
    }

    function getUser() {
        return $this->user;
    }

    function getNotificationCode() {
        return $this->notificationCode;
    }

    public function isFlush()
    {
        return $this->flush;
    }

    public function setFlush($flush)
    {
        $this->flush = $flush;
    }
}