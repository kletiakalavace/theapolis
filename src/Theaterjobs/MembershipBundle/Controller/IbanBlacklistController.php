<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\MembershipBundle\Entity\IbanBlacklist;
use Theaterjobs\MembershipBundle\Form\IbanBlacklistType;

/**
 * IbanBlacklist controller.
 *
 * @TODO check if we use this
 * @Route("/iban-blacklist")
 */
class IbanBlacklistController extends Controller
{

    /**
     * Lists all IbanBlacklist entities.
     *
     * @Route("/", name="ibanblacklist")
     * @Method({"GET", "POST"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('TheaterjobsMembershipBundle:IbanBlacklist')->findAll();
        $entity = new IbanBlacklist();
        $form = $this->createCreateForm($entity);

        return $this->render('TheaterjobsMembershipBundle:IbanBlacklist:index.html.twig', array(
            'entities' => $entities,
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new IbanBlacklist entity.
     *
     * @Route("/add", name="ibanblacklist_create")
     * @Method("POST")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $iban = $request->request->get('theaterjobs_membershipbundle_ibanblacklist')['iban'];
        $entity = $this->getDoctrine()->getRepository('TheaterjobsMembershipBundle:IbanBlacklist')->findOneBy(array('iban' => $iban));
        if ($entity) {
            $this->addFlash('ibanBlacklist', ['danger' => $this->get('translator')->trans("iban.already.in.blacklist", ['%iban%' => $iban], 'flashes')]);
            return $this->redirect($this->generateUrl('ibanblacklist'));
        } else {
            $entity = new IbanBlacklist();
        }
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('ibanBlacklist', ['success' => $this->get('translator')->trans("iban.added.in.blacklist", ['%iban%' => $iban], 'flashes')]);
            return $this->redirect($this->generateUrl('ibanblacklist'));
        }

        return $this->render('TheaterjobsMembershipBundle:IbanBlacklist:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));

    }

    /**
     * Creates a form to create a IbanBlacklist entity.
     *
     * @param IbanBlacklist $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IbanBlacklist $entity)
    {
        $form = $this->createForm(new IbanBlacklistType(), $entity, array(
            'action' => $this->generateUrl('ibanblacklist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->container->get('translator')->trans('button.create')));

        return $form;
    }

    /**
     * Deletes a IbanBlacklist entity.
     *
     * @Route("/delete/{id}", name="ibanblacklist_delete")
     * @Method("GET")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TheaterjobsMembershipBundle:IbanBlacklist')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IbanBlacklist entity.');
        }

        $em->remove($entity);
        $em->flush();
        $this->addFlash('ibanBlacklist', ['success' => $this->get('translator')->trans("iban.removed.from.blacklist", array('%iban%' => $entity->getIban()), 'flashes')]);
        return $this->redirect($this->generateUrl('ibanblacklist'));
    }

}
