<?php

namespace Theaterjobs\MainBundle\Utility\Traits;


use Theaterjobs\UserBundle\Event\MarkNotificationAsReadEvent;

trait ReadNotificationTrait
{
    /**
     * Shorthand to MarkNotificationAsReadEvent
     * @param $entity
     * @param $user
     * @param $code
     * @param $from
     * @param $flush
     */
    public function readNotification($entity, $code, $user, $from = null, $flush = true) {
        //Delete notification profile_not_updated
        $markNotificationReadEvent = new MarkNotificationAsReadEvent($entity, $code, $user, $from, $flush);
        $this->get('event_dispatcher')->dispatch("MarkNotificationAsReadEvent", $markNotificationReadEvent);
    }
}