<?php

namespace Theaterjobs\UserBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\AdminUserComments;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Handles admin actions on UserBundle
 *
 * Class UserController
 * @package Theaterjobs\AdminBundle\Controller
 *
 * @Route("/")
 */
class AdminUserCommentsController extends BaseController
{

    /**
     * Creates a new AdminUserComments entity.
     *
     * @Route("/createComment/{slug}", name="organization_admin_user_comments_create")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createUserCommentAction(Request $request, Profile $profile)
    {
        $entity = new AdminUserComments();
        $form = $this->createCreateForm('theaterjobs_userbundle_admin_user_comments', $entity, $options = [], 'tj_admin_admin_comments_create_job');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setUser($profile->getUser());
            $entity->setAdmin($this->getUser());
            $em = $this->getEM();
            $em->persist($entity);
            $em->flush();
            $comments = $em->getRepository('TheaterjobsUserBundle:AdminUserComments')->findBy(array('user' => $profile->getUser()), ['publishedAt' => 'DESC']);

            return $this->render('TheaterjobsUserBundle:Partial:userComments.html.twig', array(
                'comments' => $comments,
                'entity' => $profile
            ));
        }

        return $this->redirect($this->generateUrl('tj_user_account_settings', array('slug' => $profile->getSlug())));
    }

    /**
     * Returns all AdminUserComments .
     *
     * @Route("/all/comments/{slug}", name="tj_user_admin_user_comments_get_all")
     * @Method({"GET"})
     * @param Profile $profile
     * @return Response
     */
    public function getAllComments(Profile $profile)
    {
        $em = $this->getEM();
        $entity = $em->getRepository('TheaterjobsUserBundle:AdminUserComments')->findBy(
            array('user' => $profile->getUser()),
            ['publishedAt' => 'DESC']

        );

        return $this->render('TheaterjobsUserBundle:AccountSettings\Modal:ShowAllComments.html.twig', array(
            'comments' => $entity
        ));
    }
}