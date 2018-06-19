<?php
namespace Theaterjobs\UserBundle\Controller;

use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Controller\ResettingController as BaseController;

/**
 * Controller managing the resetting of the password
 */
class ResettingController extends BaseController
{
    /**
     * Request reset user password: show form
     */
    public function requestAction()
    {
        return $this->render('FOSUserBundle:Resetting:request.html.twig');
    }

    /**
     * Request reset user password: submit form and send email
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');
        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null === $user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $this->get('translator')->trans('ResetPassword.modal.request.invalid_username', ['%invalid_username%' => $username], 'flashes')
                ]);
            } else {
                return $this->render('FOSUserBundle:Resetting:request.html.twig', array(
                    'invalid_username' => $username
                ));
            }
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $this->get('translator')->trans('ResetPassword.modal.password_already_requested', [], 'flashes')
                ]);
            } else {
                return $this->render('FOSUserBundle:Resetting:password_already_requested.html.ig');
            }
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        /* Dispatch confirm event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        $this->get('app.mailer.twig_swift')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);


        /* Dispatch completed event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => $this->get('translator')->trans('resetpassword.check_email', ['%email%' => $this->getObfuscatedEmail($user)], 'flashes')
            ]);
        } else {
            return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email',
                array('email' => $this->getObfuscatedEmail($user))
            ));
        }
    }

    /**
     * Tell the user to check his email provider
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request'));
        }

        return $this->render('FOSUserBundle:Resetting:check_email.html.twig', array(
            'email' => $email,
        ));
    }

    /**
     * Reset user password
     *
     * @param Request $request
     * @param string $token
     *
     * @return Response
     */
    public function resetAction(Request $request, $token)
    {
        $formFactory = $this->get('fos_user.resetting.form.factory');
        $userManager = $this->get('fos_user.user_manager');
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            //Confirmation link broken
            return $this->render('TheaterjobsUserBundle:Resetting:reset_link_broken.html.twig');
        }

        //Check if confirmation token has expired
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            //Confirmation link broken
            return $this->render('TheaterjobsUserBundle:Resetting:reset_link_broken.html.twig');
        }

        $email = $user->getEmail();

        $exists = $this->getDoctrine()->getEntityManager()->getRepository('TheaterjobsUserBundle:EmailBlacklist')->findOneBy(['email' => $email]);
        //User email is banned
        if ($exists) {
            $this->addFlash('homePage', ['error' => $this->get('translator')->trans('homepage.email.reset.password.banned', [], 'messages')]);
            $url = $this->get('router')->generate('tj_main_default_home') . '#login';
            $response = new RedirectResponse($url);
            $request->request->remove('_remember_me');
            return $response;
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('FOSUserBundle:Resetting:reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}
