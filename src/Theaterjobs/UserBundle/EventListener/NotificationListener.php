<?php

namespace Theaterjobs\UserBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * StatsSubscriber.
 *
 * @category EventListener
 *
 * @author   Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_notification.listener")
 */
class NotificationListener {

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    public $em;

    /**
     * @DI\Observe("notification", priority = 255)
     * @param NotificationEvent $event
     * @throws \Exception
     */
    public function onNotification(NotificationEvent $event)
    {
        $em = $this->em;
        $type = null;
        $from = $event->getFrom();
        $notification = $event->getNotification();

        // Query to get users who get the note
        $users = $event->getUsers();

        if ($event->getType() !== null) {
            $type = $em->getRepository('TheaterjobsUserBundle:TypeOfNotification')->findOneByCode($event->getType());
        }

        // If the notification will go to only one user
        if (!is_array($users)) {
            $notification->setUser($users);
            $notification->setFrom($from);
            $notification->setSeen(false);
            $notification->setEntityClass($event->getObjectClass());
            $notification->setEntityId($event->getObjectId());
            $notification->setTypeOfNotification($type);
            $em->persist($notification);
            $users->setHasNotifications(true);
            if ($event->getFlush()) $em->flush();
            return;
        // Notification will go to many users
        }
        if (count($users) > 50) {
            // @TODO Load notifications on the queue
            return;
        }
        $i = 1;
        $batchSize = 20;

        foreach ($users as $user) {
            if (!($user instanceof User)) {
                throw new \Exception("Notification object must be filled with User types");
            }
            // Clone notification and add the user on it
            $notificationClone = clone $notification;
            $notificationClone->setUser($user);
            $notificationClone->setFrom($from);
            $notificationClone->setSeen(false);
            $notificationClone->setEntityClass($event->getObjectClass());
            $notificationClone->setEntityId($event->getObjectId());
            $notificationClone->setTypeOfNotification($type);

            $user->setHasNotifications(true);
            $em->persist($notificationClone);

            if ($i % $batchSize === 0) {
                if ($event->getFlush()) {
                    $em->flush();
                    $em->clear(Notification::class);
                }
            }
            $i++;
        }

        if ($event->getFlush()) {
            $em->flush();
        }
    }
}
