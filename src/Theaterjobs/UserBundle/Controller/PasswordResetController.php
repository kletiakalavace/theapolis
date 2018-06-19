<?php

namespace Theaterjobs\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\MainBundle\Controller\BaseController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * EmailChangeRequest controller.
 *
 * @Route("/passwordchange")
 */
class PasswordResetController extends BaseController
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /**
     * Displays a form to change a password
     *
     * @Route("/{slug}", name="tj_user_password_change_new", defaults={"slug" = null})
     * @Method("GET")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Profile $profile)
    {
        if ($profile === null) {
            $entity = $this->getUser();
        } else {
            $entity = $profile->getUser();
        }
        $form = $this->createCreateForm('tj_user_form_change_password', $entity, $options = [], 'tj_user_password_change_create');

        return $this->render('TheaterjobsUserBundle:PasswordReset:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }
    /**
     * Creates a new EmailChangeRequest entity.
     *
     * @Route("/{slug}", name="tj_user_password_change_create", defaults={"slug" = null})
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {


        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $entity = $this->getUser();
        $form = $this->createCreateForm('tj_user_form_change_password', $entity, $options = [], 'tj_user_password_change_create');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $pass = $request->request->get('tj_user_form_change_password')['password'];
            $first = $request->request->get('tj_user_form_change_password')['plainPassword']['first'];
            $second = $request->request->get('tj_user_form_change_password')['plainPassword']['second'];

            $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
            $encodedPass = $encoder->encodePassword($pass, $entity->getSalt());

            $userExist = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array("password" => $encodedPass));

            if ((count($userExist) == 1) && ($first === $second))
            {
                $now = new \DateTime();
                $entity->setPasswordLastEditAt($now);
                $userManager->updateUser($entity);
                $dispatcher = $this->get('event_dispatcher');
                $uacEvent = new UserActivityEvent($entity, $this->get('translator')->trans('tj.user.activity.changed.password', array(), 'activity'));
                $dispatcher->dispatch("UserActivityEvent", $uacEvent);


                $emailContent = $this->render('@TheaterjobsUser/Mailer/passwordChange.twig', [
                    'profile' => $entity->getProfile(),
                    'content1' => $this->get('translator')->trans('tj.email.text.password.changed.content1', array(), 'emails'),
                    'content2' => $this->get('translator')->trans('tj.email.text.password.changed.content2', array(), 'emails')
                ])->getContent();

                $this->get('base_mailer')->sendEmailMessage(
                        $this->get('translator')->trans('tj.email.password.changed.subject', array(), 'emails'),
                        $emailContent,
                        $this->container->getParameter('resetting_from_email_address'),
                        $entity->getEmail()
                    );

                if ($userExist->getProfile()->getSlug() === $this->getUser()->getProfile()->getSlug()) {
                    return new JsonResponse([
                        'error' => false,
                        'message' => 'Password was successfully changed.',
                        'data' => $now->format('d.m.Y')
                    ]);
                }
            }
            else
            {
                $errorBag = [];

                if(count($userExist) == 0)
                {array_push($errorBag,['field'=>'#tj_user_form_change_password_password','message'=>'The current password you entered is not correct.']);}

                if($first != $second)
                {array_push($errorBag,['field'=>'#tj_user_form_change_password_plainPassword_second','message'=>'Password confirmation doesn\'t match to new password.']);}

                return new JsonResponse([
                    'error' => true,
                    'errors' => $errorBag
                ]);
            }
        }

        return $this->render('TheaterjobsUserBundle:PasswordReset:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Lists all Job entities.
     *
     * @param $pass
     * @param Profile $profile
     * @return JsonResponse $array
     *
     * @Route("/passcheck/{pass}", name="tj_user_password_change_check", options={"expose"=true})
     * @Route("/passcheck/{slug}/{pass}", name="tj_admin_password_change_check", options={"expose"=true})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "POST"})
     */
    public function checkPassAction($pass, Profile $profile)
    {
        if ($profile === null) {
            $user = $this->getUser();
        } else {
            $user = $profile->getUser();
        }
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $encodedPass = $encoder->encodePassword($pass, $user->getSalt());

        $userExist = $this->em->getRepository('TheaterjobsUserBundle:User')
            ->findOneBy(array("password" => $encodedPass));

        $exist = false;
        if (count($userExist) == 1) {
            $exist = true;
        }

        return new JsonResponse($exist);
    }

}
