<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\InserateBundle\Entity\AdminComments;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * AdminComments controller.
 *
 * @Route("/comments")
 */
class AdminCommentsController extends BaseController
{
    /**
     * Creates a new AdminComments entity.
     *
     * @Route("/inserate", name="tj_admin_admin_comments_create_job")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createInserateCommentAction(Request $request)
    {
        $entity = new AdminComments();
        $form = $this->createCreateForm('tj_admin_job_admin_comments', $entity, [], 'tj_admin_admin_comments_create_job');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setUser($this->getUser());
            $em = $this->getEM();
            $inserate = $entity->getInserate();
            $inserate->addAdminComment($entity);
            $inserate->setUpdatedAt(new \DateTime());
            $em->persist($inserate);
            $em->persist($entity);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->render('TheaterjobsInserateBundle:Partial:jobComments.html.twig', array(
                    'entity' => $inserate
                ));
            }
        }

        // @todo To be checked the redirection in case is an  Education or a Network
        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', array('slug' => $entity->getInserate()->getSlug())));
    }

    /**
     * Creates a new AdminComments entity.
     *
     * @Route("/organization", name="tj_admin_admin_comments_create_orga")
     * @Method("POST")
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createOrgaCommentAction(Request $request)
    {
        $entity = new AdminComments();
        $form = $this->createCreateForm('tj_admin_admin_comments_create_orga', $entity, $options = [], 'tj_admin_admin_comments_create_orga');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setUser($this->getUser());
            $em = $this->getEM();
            $organization = $entity->getOrganization();
            $organization->addAdminComment($entity);
            $organization->setUpdatedAt(new \DateTime());

            $em->persist($organization);
            $em->persist($entity);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->render('TheaterjobsInserateBundle:Partial:organizationComments.html.twig', array(
                    'entity' => $organization
                ));
            }

            if ($entity->getOrganization())
                return $this->redirect($this->generateUrl('tj_organization_show', array('slug' => $entity->getOrganization()->getSlug())));
        }

        return $this->redirect($this->generateUrl('tj_organization_show', array('slug' => $entity->getOrganization()->getSlug())));
    }
}
