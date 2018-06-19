<?php

namespace Theaterjobs\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class UserActivityEvent extends Event {

    protected $userActivity;
    
    protected $text;
    
    protected $changedFields;
    
    protected $forAdmin;

    protected $user;

    protected $flush;

    public function __construct($userActivity,$text, $forAdmin = false, $changedFields = null, $user = null, $flush = true) {
        $this->userActivity = $userActivity;
        $this->text = $text;
        $this->changedFields = $changedFields;
        $this->forAdmin = $forAdmin;
        $this->user = $user;
        $this->flush = $flush;
    }
    
    public function getUserActivity()
    {
        return $this->userActivity;
    }
    
    public function getText(){
        return $this->text;
    }
    
    function getChangedFields() {
        return $this->changedFields;
    }
    
    function getForAdmin() {
        return $this->forAdmin;
    }

    public function getUser(){
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isFlush()
    {
        return $this->flush;
    }

    /**
     * @param bool $flush
     */
    public function setFlush($flush)
    {
        $this->flush = $flush;
    }
}