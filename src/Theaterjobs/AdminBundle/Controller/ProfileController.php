<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\EmailBlacklist;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * Description of ProfileController
 *
 * @Route("/profile")
 */
class ProfileController extends BaseController
{
    /**
     * Blacklists a user email
     *
     * @Route(
     *     "/block/{slug}/{action}",
     *     name="tj_admin_block_email",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     requirements={"action": "[0-1]"}
     *     )
     *
     *
     *
     * @param Profile $profile
     * @param $action
     *
     * @return JsonResponse
     */
    public function blockEmailAction(Profile $profile, $action)
    {
        $em = $this->getEM();
        $exists = $em->getRepository(EmailBlacklist::class)->findOneBy([
            'admin' => $this->getUser()->getId(),
            'email' => $profile->getUser()->getEmail()
        ]);

        //Action 1 => Blacklist email
        if ($action == 1) {
            if (!$exists) {
                $blackE = new EmailBlacklist();
                $blackE->setEmail($profile->getUser()->getEmail());
                $blackE->setCreatedAt(Carbon::now());
                $blackE->setAdmin($this->getUser()->getId());
                $em->persist($blackE);
                $em->flush();

                $uacEvent = new UserActivityEvent($profile->getUser(), $this->getTranslator()->trans('user.activity.admin.blocked.email', [], 'activity'));
                $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

                $result = [
                    'error' => false,
                    'message' => $this->getTranslator()->trans('flash.success.emailBlacklisted')
                ];
            } else {
                $result = [
                    'error' => true,
                    'message' => $this->getTranslator()->trans('flash.success.emailAlreadyBlacklisted')
                ];
            }
        } //Action 0 => Remove blacklist email
        else {
            if ($exists) {
                $em->remove($exists);
                $em->flush();

                $uacEvent = new UserActivityEvent($profile->getUser(), $this->getTranslator()->trans('user.activity.admin.unblocked.email', [], 'activity'));
                $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

                $result = [
                    'error' => false,
                    'message' => $this->getTranslator()->trans('flash.success.removedEmailBlacklist')
                ];
            } else {
                $result = [
                    'error' => true,
                    'message' => $this->getTranslator()->trans('flash.error.emailNotBlacklisted')
                ];
            }
        }
        return new JsonResponse($result);
    }
}
