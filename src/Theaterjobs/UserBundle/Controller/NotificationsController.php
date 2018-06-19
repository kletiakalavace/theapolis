<?php

namespace Theaterjobs\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Entity\Notification;


/**
 * NotificationSettings controller.
 *
 * @category Controller
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 *
 * @Route("/notifications")
 */
class NotificationsController extends BaseController
{
    use ScheduleUpdateESIndexTrait;

    /**
     * Lists all Notifications.
     *
     * @Route("/index", name="tj_user_notifications")
     * @Method("GET")
     *
     * @return Response
     */
    public function indexAction()
    {
        //Get current user
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        //Get All notification seen/unseen and not hidden
        $allNotifications = $em->getRepository('TheaterjobsUserBundle:Notification')
            ->findBy(
                ['user' => $this->getUser()],
                ['createdAt' => 'DESC']
            );
        //Make seen all of them
        if ($user->getHasNotifications()) {
            $user->setHasNotifications(false);
            $em->flush($user);
        }
        //Nr of affected rows
        $ids = $em->getRepository(Notification::class)->makeSeenIds($user);
        $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, Notification::class, $ids, 'app', true);

        $nrUnseenNotification = count($ids);
        $requiredNotific = [];
        $notific = [];
        foreach ($allNotifications as $notification) {
            if ($notification->getRequireAction()) {
                $requiredNotific[] = $notification;
            } else {
                $notific[] = $notification;
            }
        }
        $totalNrNotification = count($allNotifications);
        return $this->render(
            'TheaterjobsUserBundle:Notifications:index.html.twig',
            [
                'requiredNotification' => $requiredNotific,
                'notification' => $notific,
                'nrUnseenNotification' => $nrUnseenNotification,
                'totalNrNotification' => $totalNrNotification
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="tj_user_notification_delete", options={"expose"=true}), condition="request.isXmlHttpRequest()")
     * @Method("DELETE")
     * @param Notification $notification
     * @return JsonResponse
     */
    public function deleteAction(Notification $notification)
    {
        if ($notification->getRequireAction()) {
            return new JsonResponse([
                'success' => false,
                'error' => $this->getTranslator()->trans('nra.notification.cant.be.deleted')
            ]);
        }
        $em = $this->getEM();
        $em->remove($notification);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }
}