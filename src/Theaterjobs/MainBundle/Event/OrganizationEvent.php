<?php

namespace Theaterjobs\MainBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OrganizationEvent extends Event {

    public function __construct($object) {
        $this->object = $object;
    }

    protected $object;

    public function getObject() {
        return $this->object;
    }

}
