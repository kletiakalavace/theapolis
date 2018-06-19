<?php

namespace Theaterjobs\UserBundle\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\UserBundle\Event\MarkNotificationAsReadEvent;

/**
 * StatsSubscriber.
 *
 * @category EventListener
 *
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_marknotificationread.mark_read_listener")
 */
class MarkNotificationAsReadListener {

    /**
     * @var AuthorizationChecker;
     */
    private $securityContext;

    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.authorization_checker"),
     *     "redis" = @DI\Inject("snc_redis.default"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param AuthorizationChecker $securityContext
     * @param $redis
     * @param EntityManager $em
     */
    public function __construct(AuthorizationChecker $securityContext, $redis, EntityManager $em) {
        $this->securityContext = $securityContext;
        $this->redis = $redis;
        $this->em = $em;
    }

    /**
     * @DI\Observe("MarkNotificationAsReadEvent", priority = 256)
     */
    public function onMarkNotificationAsRead(MarkNotificationAsReadEvent $event) {
        if ($event->getUser() !== null) {
            $notificationCheck = $this->em->createQueryBuilder()->select('notification')->from("TheaterjobsUserBundle:Notification", 'notification')
                            ->innerJoin('notification.typeOfNotification', 'notType')
                            ->where('notification.entityClass = :class')
                            ->andWhere('notification.entityId = :notId')
                            ->andWhere('notification.user = :user')
                            ->andWhere('notType.code = :notCode')
                            ->setParameters(array(
                                'class' => str_replace("Proxies\__CG__\\", "", get_class($event->getObject())),
                                'notId' => $event->getObject()->getId(),
                                'user' => $event->getUser(),
                                'notCode' => $event->getNotificationCode()
                            ))->getQuery()->getResult();
        } elseif ($event->getFrom() !== null) {
            $notificationCheck = $this->em->createQueryBuilder()->select('notification')->from("TheaterjobsUserBundle:Notification", 'notification')
                            ->innerJoin('notification.typeOfNotification', 'notType')
                            ->where('notification.entityClass = :class')
                            ->andWhere('notification.entityId = :notId')
                            ->andWhere('notification.from = :from')
                            ->andWhere('notType.code = :notCode')
                            ->setParameters(array(
                                'class' => str_replace("Proxies\__CG__\\", "", get_class($event->getObject())),
                                'notId' => $event->getObject()->getId(),
                                'from' => $event->getFrom(),
                                'notCode' => $event->getNotificationCode()
                            ))->getQuery()->getResult();
        } elseif ($event->getNotificationCode() === null) { // reject or archive job application, close profile communication - remove all notifications
            $notificationCheck = $this->em->createQueryBuilder()->select('notification')->from("TheaterjobsUserBundle:Notification", 'notification')
                            ->where('notification.entityClass = :class')
                            ->andWhere('notification.entityId = :notId')
                            ->setParameters(array(
                                'class' => str_replace("Proxies\__CG__\\", "", get_class($event->getObject())),
                                'notId' => $event->getObject()->getId(),
                            ))->getQuery()->getResult();
        }

        foreach ($notificationCheck as $notification) {
            //$nofication->setSeen(true);
            //$this->em->persist($notification);
            $this->em->remove($notification);
        }
        if ($event->isFlush()) {
            $this->em->flush();
        }
    }

}
