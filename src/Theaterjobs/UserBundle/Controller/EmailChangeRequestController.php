<?php

namespace Theaterjobs\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Theaterjobs\MainBundle\Utility\LoggerInterface;
use Theaterjobs\UserBundle\Entity\EmailChangeRequest;
use Theaterjobs\UserBundle\Event\MarkNotificationAsReadEvent;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Theaterjobs\UserBundle\Form\EmailChangeRequestType;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * EmailChangeRequest controller.
 *
 * @Route("/email_change_request")
 */
class EmailChangeRequestController extends Controller
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /**
     * Displays a form to create a new EmailChangeRequest entity.
     *
     * @Route("/new/{slug}", name="email_change_request_new", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Profile $profile = null)
    {
        $user = $profile === null ? $this->getUser() : $profile->getUser();
        $entity = new EmailChangeRequest();
        $form = $this->createCreateForm($entity, $user->getProfile()->getSlug());
        $form->get('oldMail')->setData($user->getEmail());
        $emailWarning = $user->getProfile()->getProfileAllowedTo()->getEmailWarning();

        return $this->render('TheaterjobsUserBundle:EmailChangeRequest:new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'profile' => $user->getProfile(),
            'emailWarning' => $emailWarning
        ]);
    }

    /**
     * Creates a new EmailChangeRequest entity.
     *
     * @Route("/create/{slug}", name="email_change_request_create", defaults={"slug" = null})
     * @Method("POST")
     * @param Request $request
     * @param Profile $profile
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAction(Request $request, Profile $profile = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ttl = $this->getParameter('account_settings_email_expiration');

        if ($profile === null) {
            $user = $this->getUser();
        } else {
            $user = $profile->getUser();
        }

        $entity = $this->getDoctrine()->getManager()->getRepository(EmailChangeRequest::class)->findOneBy(array('userId' => $user->getId()));
        if (!$entity) {
            $entity = new EmailChangeRequest();
        } else {
            if ($entity->isChangeEmailRequestExpired($ttl)) {
                $em->remove($entity);
                $em->flush();
                $entity = new EmailChangeRequest();
            } else {
                $msg = $this->get('translator')->trans('accountSettings.emailChangeRequest.already.requested', array(), 'flashes');
                return new JsonResponse(array('error' => true,
                    'errors'=>[
                        ['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]
                    ]
                ));
            }
        }

        $form = $this->createCreateForm($entity, $user->getProfile()->getSlug());
        $form->handleRequest($request);

        if ($form->isValid()){
            $errorBag = array();

            //Requesting same old mail
            if ($form->get('newMail')->getData() == $user->getEmail()){
                $msg = $this->get('translator')->trans('flash.new.email.addreses.same.oldMail', array(), 'flashes');
                array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]);
            }
            //Invalid mail
            elseif ($this->checkFalseEmail($form->get('newMail')->getData())) {
                $msg = $this->get('translator')->trans('accountSettings.emailChangeRequest.provider.notFound', array(), 'flashes');
                array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]);
            }
            //Blacklisted email
            elseif ($this->checkBlacklistedEmail($form->get('newMail')->getData())) {
                $msg = $this->get('translator')->trans('accountSettings.emailChangeRequest.banned.byAdmin', array(), 'flashes');
                array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]);
            }
            //Check if existing email on users
            elseif ($this->checkIsUsed($form->get('newMail')->getData())) {
                $msg = $this->get('translator')->trans('accountSettings.emailChangeRequest.email.already.used', array(), 'flashes');
                array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]);
            }
            //check if already requested email by others
            $already = $em->getRepository(EmailChangeRequest::class)->findOneBy(['newMail' => $entity->getNewMail()]);
            if ($already) {
                //If already used link has expired , can be used by others
                if ($already->isChangeEmailRequestExpired($ttl)) {
                    $em->remove($already);
                    $em->flush();
                } else {
                    $msg = $this->get('translator')->trans('accountSettings.emailChangeRequest.already.requested.is.used', array(), 'flashes');
                    array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$msg]);
                }
            }

            if (count($errorBag)>0){
                $response = new JsonResponse(array('error' => true,'errors'=>$errorBag));
                return $response;
            }

            $entity->setUserId($user->getId());
            $mail = $form->get('newMail')->getData();
            $confirmationToken = $generatedKey = sha1(mt_rand(10000, 99999) . time() . $mail);
            $entity->setConfirmationToken($confirmationToken);
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if ($user->getProfile()->getProfileAllowedTo()->getEmailWarning()) {

                $user->getProfile()->getProfileAllowedTo()->setEmailWarning(false);
                $em->persist($user->getProfile());
                $em->flush();
                $markNotReadEvent = new MarkNotificationAsReadEvent($user->getProfile(), 'email_notifications_revoked', $user);
                $this->get('event_dispatcher')->dispatch("MarkNotificationAsReadEvent", $markNotReadEvent);
            }

            $url = $this->generateUrl('tj_user_confirm_new_mail', array('token' => $confirmationToken, 'user_id' => $user->getId()), true);
            $this->get('base_mailer')
                ->sendEmailMessage(
                    $this->get('translator')->trans('changeMail.email.subject', array(), 'emails'),
                    $this->renderView('TheaterjobsUserBundle::EmailChangeRequest/checkMail.html.twig', array('confirmationUrl' => $url,'user' => $user)),
                    'info@theapolis.de',
                    $entity->getNewMail(),
                    'text/html'
                );

            return new JsonResponse (['error' => false,'message'=>$this->get('translator')->trans('account.settings.email.changed.successfully', [], 'flashes')]);
        }
        else
        {
            $errorBag = [];
            array_push($errorBag,['field'=>'#theaterjobs_userbundle_emailchangerequest_newMail_first','message'=>$this->get('translator')->trans('flash.new.email.addreses.dont.match', array(), 'flashes')]);
            return new JsonResponse (['error' => true,'errors'=>$errorBag]);
        }
    }

    /**
     * Creates a form to create a EmailChangeRequest entity.
     *
     * @param EmailChangeRequest $entity The entity
     *
     * @param null $slug
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EmailChangeRequest $entity, $slug = null)
    {
        $form = $this->createForm(new EmailChangeRequestType(), $entity, array(
            'action' => $this->generateUrl('email_change_request_create', array('slug' => $slug)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('button.create', array(), 'forms')));

        return $form;
    }

    /**
     * Handles email confirmation
     *
     * @Route("/confirm/{token}/{user_id}", name="tj_user_confirm_new_mail")
     * @Method("GET")
     */
    public function confirmNewMailAction($token, $user_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository(EmailChangeRequest::class)->findOneBy(array(
            'userId' => $user_id,
            'confirmationToken' => $token,
        ));

        //Other logged in user trying to confirm someones confirmation link
        if ($this->getUser() && $this->getUser()->getId() != $user_id){
            throw new NotFoundHttpException();
        }

        if ($entity) {
            $user = $em->getRepository('TheaterjobsUserBundle:User')->find($user_id);
            if ($entity->isChangeEmailRequestExpired($this->getParameter('account_settings_email_expiration'))) {
                $this->get('session')->getFlashBag()->add('accountSettings',
                    ['danger' => $this->get('translator')->trans('accountSettings.emailChangeRequest.expired', [], 'flashes')]
                );
                return $this->redirect($this->generateUrl('tj_user_account_settings', ['slug' => $user->getProfile()->getSlug()]));
            }

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($entity, $this->get('translator')->trans('tj.user.activity.changed.email.address', [], 'activity'), json_encode(["old" => $entity->getOldMail(), "new" => $entity->getNewMail()]));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $user->setEmail($entity->getNewMail());
            $user->setUsername($entity->getNewMail());
            $user->getProfile()->getProfileAllowedTo()->setEmailWarning(false);
            $em->remove($entity);
            $em->flush();
            //Log out user
            $this->get('security.token_storage')->setToken(null);
            $this->get('request')->getSession()->invalidate();

            //Check for invalid email notification and delete
            $type = $em->getRepository('TheaterjobsUserBundle:TypeOfNotification')->findOneBy(array('code' => 'renew_email'));
            $exists = $em->getRepository('TheaterjobsUserBundle:Notification')->findOneBy(array('user' => $user,'typeOfNotification' => $type));
            if($exists){
                $em->remove($exists);
                $em->flush();
            }
            return $this->render('TheaterjobsUserBundle:EmailChangeRequest:confirmedEmail.html.twig');
        }
        throw new NotFoundHttpException();
    }

    /**
     * Check if email is temporary or false.
     */
    public function checkFalseEmail($email)
    {

        $domain = explode("@",$email)[1];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://www.mogelmail.de/q/'.$domain
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }


    /**
     * Check if email is blacklisted or false.
     * @param $email
     * @return mixed
     */
    public function checkBlacklistedEmail($email)
    {
        if ($this->getDoctrine()->getManager()->getRepository('TheaterjobsUserBundle:EmailBlacklist')->checkForBannedEmail($email)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if email is blacklisted or false.
     * @param $email
     * @return mixed
     */
    public function checkIsUsed($email)
    {
        if ($this->getDoctrine()->getManager()->getRepository('TheaterjobsUserBundle:User')->findOneBy(['email' => $email])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if email is blacklisted or false.
     * @param $oldEmail
     * @param $newEmail
     * @return mixed
     */
    public function checkIsEmailChangeRequestPending($oldEmail, $newEmail)
    {
        $exists = $this->getDoctrine()->getEntityManager()->getRepository(EmailChangeRequest::class)->findOneBy(
            ['newMail' => $newEmail, 'oldMail' => $oldEmail]
        );
        return !empty($exists);
    }

    /**
     * Mark a user his email as fixed and remove the nra notification and setEmail valid to true
     *
     * @Route("/email/fix/{slug}", name="tj_user_email_change_fix", options={"expose"=true})
     * @Method("GET")
     */
    public function fixEmailAction(Profile $profile = null)
    {
        if ($profile === null || !$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
        } else {
            $user = $profile->getUser();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $profile->getProfileAllowedTo()->setEmailWarning(false);
        $type = $em->getRepository('TheaterjobsUserBundle:TypeOfNotification')->findOneBy(array('code' => 'renew_email'));
        $exists = $em->getRepository('TheaterjobsUserBundle:Notification')->findOneBy(array('user' => $user,'typeOfNotification' => $type));
        if($exists){
            $em->remove($exists);
        }
        $em->flush();
        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans("email.marked.as.fixed")
        ]);
    }
}
