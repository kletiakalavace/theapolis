<?php

namespace Theaterjobs\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Carbon\Carbon;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegistrationController extends BaseController
{
    public function registerAction(Request $request)
    {
        $choice = $request->get('choice');
        if (!$choice) {
            throw new NotFoundHttpException();
        }

        $this->get('session')->set('registrationChoice', $choice);
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);
        $emailCustomErrors = array();
        if ($form->isSubmitted()) {

            if ($this->getRequest()->getHost() != 'design.theater-leute.de') {
                $isFalseEmail = $this->checkFalseEmail($form->getData()->getEmail());
            } else {
                $isFalseEmail = false;
            }
            $isBlacklisted = $this->checkBlacklistedEmail($form->getData()->getEmail());
            //$isRegisteredButNotConfirmed = $this->checkEmailRegisteredButNotConfirmed($form->getData()->getEmail());

            if ($isBlacklisted) {
                $emailCustomErrors[] = ['error' => 'blacklisted', 'message' => $this->get('translator')->trans('registration.email.banned.byAdmins', [], 'messages')];
            }

            if ($isFalseEmail) {
                $emailCustomErrors[] = ['error' => 'falseEmail', 'message' => $this->get('translator')->trans('registration.email.notValid', [], 'messages')];
            }

            foreach ($form->all() as $key => $child) {
                if ($key == 'email' && !$child->isValid()) {
                    foreach ($child->getErrors() as $key1 => $error) {
                        $emailCustomErrors[] = ['error' => 'emailFormError' . $key1, 'message' => $error->getMessage()];
                    }
                }
            }

            if (count($emailCustomErrors) == 0 && $form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return new RedirectResponse($this->generateUrl('fos_user_registration_check_email_choice', ['choice' => $choice]));
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', [
            'emailCustomErrors' => json_encode($emailCustomErrors),
            'form' => $form->createView(),
            'choice' => $choice
        ]);
    }

    /**
     *
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notification = $em->getRepository(Notification::class)->findBy([
            'user' => $this->getUser(),
            'typeOfNotification' => 'become_member'
        ]);
        $request = $this->getRequest();
        $choice = $request->get('choice');
        if ($notification) {
            return $this->redirectToRoute('tj_main_dashboard_index', ['choice' => $choice]);
        }
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $domain = null;
        $notification = new Notification();

        $title = 'tj.notification.become.member';
        $describ = 'tj.notification.become.member.to.access.job.market';

        $notification->setTitle($title);
        $notification->setDescription($describ);
        $notification->setCreatedAt(Carbon::now());
        $notification->setRequireAction(true);
        $notification->setLink('tj_membership_booking_new');

        $event = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($user->getId())
            ->setNotification($notification)
            ->setFrom($this->getUser())
            ->setUsers($this->getUser())
            ->setType('become_member');

        $this->get('event_dispatcher')->dispatch('notification', $event);

        $anonNot = new Notification();
        $anonNot->setTitle('tj.notification.surf.anonymos.or.not')
            ->setDescription('')
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setLink('tj_user_account_settings');

        $anonNotEvent = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($user->getId())
            ->setNotification($anonNot)
            ->setFrom($this->getUser())
            ->setUsers($this->getUser())
            ->setType('surf_anonymously');

        $this->get('event_dispatcher')->dispatch('notification', $anonNotEvent);

        $response = $this->redirectToRoute('tj_main_dashboard_index', ['choice' => $choice]);
        return $response;
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     * @param Request $request
     * @param string $token
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->render('TheaterjobsUserBundle:Registration:checkEmail.html.twig', array('state' => 'confirmationLinkBroken'));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->addRole('ROLE_USER');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('fos_user_registration_confirmed_choice', ['choice' => $request->get('choice')]);
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * Checks if the domain of the email address of the user is equal with a domain of our organizations
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function isInstitutionEmailAvailable(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $domain = substr($email, $pos + 1);
            $organization = $this->getDoctrine()->getManager()->getRepository('TheaterjobsInserateBundle:Organization')->findOneByEmailDomain(array('domain' => $domain));
        }

        return $organization;
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('fos_user_registration_register_choise'));
        }

        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('TheaterjobsUserBundle:Registration:checkEmail.html.twig', array(
            'user' => $user,
            'state' => 'confirmationSent'
        ));
    }

    /**
     * Check if email is temporary or false.
     * @param $email
     * @return mixed
     */
    public function checkFalseEmail($email)
    {
        $mogelKey = $this->container->getParameter('mogelmail_key');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => sprintf('https://www.mogelmail.de/api/v1/%s/email/%s', $mogelKey, $email)
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($resp, true);

        if (isset($response['suspected']) && $response['suspected'] == true) {
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
    public function checkBlacklistedEmail($email)
    {
        if ($this->getDoctrine()->getManager()->getRepository('TheaterjobsUserBundle:EmailBlacklist')->checkForBannedEmail($email)) {
            return true;
        } else {
            return false;
        }
    }


    public function checkEmailRegisteredButNotConfirmed($email)
    {
        if ($this->getDoctrine()->getManager()->getRepository('TheaterjobsUserBundle:User')->checkRegisteredButNotConfirmed($email)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @Route("/register/resend_confirmation/{email}", name="resend_confirmation")
     * @param $email
     * @return Response | NotFoundHttpException
     */
    public function resendConfirmationEmail($email)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($email);

        if (!$user) {
            return new NotFoundHttpException();
        }

        $mailer = $this->get('app.mailer.twig_swift');
        $mailer->sendConfirmationEmailMessage($user);
        return $this->render('TheaterjobsUserBundle:Registration:checkEmail.html.twig', array('user' => $user, 'state' => 'confirmationResent'));
    }

}
