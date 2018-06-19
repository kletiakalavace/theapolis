<?php

namespace Theaterjobs\ProfileBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Entity\ProductionParticipations;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Form\Type\ProductionType;
use Theaterjobs\ProfileBundle\Form\Type\ProductionParticipationsType;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * Productions controller.
 *
 * @Route("/productions", options={"i18n" = false})
 */
class ProductionController extends BaseController
{
    /**
     * @DI\Inject("%theaterjobs_profile.category.profile.root_slug%")
     */
    protected $jobcategoryRoot;

    /** @DI\Inject("knp_paginator") */
    private $paginator;

    /**
     * Displays a form to create a new Production entity.
     *
     * @Route("/participations/new", name="tj_profile_participation_new", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newParticipationAction()
    {
        $em = $this->getEM();
        $categories = $this->getJobCategories($em);
        $options = [
            'category_choice_list' => $categories,
            'profile' => $this->getProfile()
        ];
        $formName = "theaterjobs_profilebundle_productionparticipations";
        $participation = new ProductionParticipations();
        $form = $this->createCreateForm($formName, $participation, $options, 'tj_profile_participation_create');

        return $this->render('TheaterjobsProfileBundle:Modal/new:production.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Creates a new Production entity.
     *
     * @Route("/participations", name="tj_profile_participation_create")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createParticipationAction(Request $request)
    {
        $em = $this->getEM();
        $profile = $this->getProfile();
        $categories = $this->getJobCategories($em);
        $options = ['category_choice_list' => $categories];
        $formName = "theaterjobs_profilebundle_productionparticipations";
        $participation = new ProductionParticipations();
        $form = $this->createCreateForm($formName, $participation, $options, 'tj_profile_participation_create');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $participation = $form->getData();
            $participation->setProfile($profile);
            $this->removeOldCategory($profile, $participation);
            $em->persist($participation);
            $this->updateProfile($profile);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse($this->returnPartial($profile));
            } else {
                return $this->redirect($this->generateUrl('tj_profile_profile_show', ['slug' => $profile->getSlug()]));
            }
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(
                [
                    'errors' => $this->getErrorMessages($form)
                ]
            );
        }


        return $this->render('TheaterjobsProfileBundle:Modal/new:production.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }

    private function removeOldCategory(Profile $profile, ProductionParticipations $participation)
    {
        if ($participation->getOccupation()) {
            foreach ($profile->getOldCategories() as $category) {
                $profile->removeOldCategory($category);
            }
        }
    }

    /**
     * Displays a form to edit an existing Participation entity.
     *
     * @Route("/participations/{id}/edit", name="tj_profile_participation_edit")
     * @Method("GET")
     */
    public function editParticipationAction(ProductionParticipations $participation)
    {
        $em = $this->getEM();
        $production = $participation->getProduction();
        $categories = $this->getJobCategories($em);

        $options = ['category_choice_list' => $categories];
        if (true === $production->getChecked()) {
            $options['intention'] = 'edit';
        }

        $formName = "theaterjobs_profilebundle_productionparticipations";
        $editForm = $this->createEditForm($formName, $participation, $options, 'tj_profile_participation_update', ['id' => $participation->getId()]);
        $deleteForm = $this->createDeleteDeleteForm($participation);

        return $this->render('TheaterjobsProfileBundle:Modal/edit:production.html.twig', [
            'participation' => $participation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Updates a new Participation entity.
     *
     * @Route("/participations/update/{id}", name="tj_profile_participation_update")
     * @Method("PUT")
     * @param Request $request
     * @param $participation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateParticipationAction(Request $request, ProductionParticipations $participation)
    {

        $em = $this->getEM();
        $profile = $this->getProfile();
        $categories = $this->getJobCategories($em);
        $options = ['category_choice_list' => $categories];

        if ($participation->getProduction()->getChecked()) {
            $options['intention'] = 'edit';
        }
        $formName = "theaterjobs_profilebundle_productionparticipations";
        $editForm = $this->createEditForm($formName, $participation, $options, 'tj_profile_participation_update', ['id' => $participation->getId()]);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->removeOldCategory($profile, $participation);
            //Update profile
            $this->updateProfile($profile, false);
            $this->getEM()->flush();
            return new JsonResponse($this->returnPartial($profile));
        }
        return new JsonResponse([
            'errors' => $this->getErrorMessages($editForm)
        ]);
    }

    /**
     * Creates a form to delete a Participation entity.
     *
     * @param ProductionParticipations $participation
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteDeleteForm(ProductionParticipations $participation)
    {
        if (!$participation) {
            throw $this->createNotFoundException('Unable to find Participation entity.');
        }

        return $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'tj_profile_participation_delete', array('id' => $participation->getId()
                    )
                )
            )
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('button.delete', array(), 'forms')))
            ->getForm();
    }

    /**
     * @param Profile $profile
     * @return array
     */
    public function returnPartial(Profile $profile)
    {
        $allInone = ProfileController::allInOne($profile, 6);
        $isOwner = $this->getUser()->isEqual($profile->getUser());

        return [
            'productions' => $this->render('TheaterjobsProfileBundle:Partial:productionPartial.html.twig', [
                'participations' => $allInone['participations'],
                'entity' => $profile,
                'owner' => $isOwner
            ])->getContent(),
            'boxes' => $this->render('TheaterjobsProfileBundle:Partial:profileBoxes.html.twig', [
                'yearsField' => $allInone['yearsInField'],
                'entity' => $profile,
                'owner' => $isOwner
            ])->getContent()
        ];
    }

    /**
     * Deletes a Participation entity.
     *
     * @Route(
     *     "/participations/{id}/delete",
     *     name="tj_profile_participation_delete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("DELETE")
     *
     * @param Request $request
     * @param ProductionParticipations $participation
     * @return mixed
     */
    public function deleteParticipationAction(Request $request, ProductionParticipations $participation)
    {

        $profile = $this->getProfile();
        $em = $this->getEM();

        $form = $this->createDeleteDeleteForm($participation);
        $form->handleRequest($request);
        $isAble = $this->isAbleToDeleteSection($profile, $participation);

        if ($form->isValid()) {
            if ($isAble) {
                $production = $participation->getProduction();
                if (false === $production->getChecked() && count($production->getParticipations()) == 1) {
                    $em->remove($production);
                } else {
                    $production->removeParticipation($participation);
                    $em->persist($production);
                }

                $em->remove($participation);

                //Update profile
                $this->updateProfile($profile, false);
                $em->flush();

                $result = [
                    'success' => true,
                    'data' => $this->returnPartial($profile)
                ];
                return new JsonResponse($result);

            } else {
                $result = [
                    'success' => false,
                    'messages' => array($this->getTranslator()->trans(
                        'profile.flash.error.unpublished.first'
                    ))
                ];
                return new JsonResponse($result);
            }
        } else {
            $result = [
                'error' => true,
                'messages' => $this->getErrorMessagesAJAX($form)
            ];
            return new JsonResponse($result);
        }
    }

    /**
     * Gets job categories.
     */
    private function getJobCategories($em)
    {
        $choiceList = $em->getRepository('TheaterjobsCategoryBundle:Category')->findChoiceListBySlug($this->jobcategoryRoot);
        return new ObjectChoiceList($choiceList, 'title', array(), null, 'id');
    }

    /**
     *Get organizations for the autosuggested field in the form
     *
     * @Route("/organization", name="tj_productions_organization", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestOrganizationsAction(Request $request)
    {
        $orgaName = $request->query->get('q');
        $organizations = $this->getRepository("TheaterjobsInserateBundle:Organization")->findLikeNameProduction($orgaName);

        $results = [];

        $pagination = $this->paginator->paginate(
            $organizations, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );


        foreach ($pagination as $organization) {

            $results[] = [
                'id' => $organization->getName(),
                'text' => $organization->getName(),
                'total_count' => $pagination->getTotalItemCount(),
                'desc' => ($organization->getMergedTo() !== null) > 0 ? $this->get('translator')->trans("tj.organization.formely.known.as %orgaTitle%", array('%orgaTitle%' => $organization->getMergedTo()->getName()), 'messages') : ""

            ];
        }

        return new JsonResponse($results);
    }

    /**
     * Get productions for the autosuggestion field in the form
     *
     * @Route("/autosuggestion", name="tj_profile_productions_autosuggestion", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function productionsSuggestAction(Request $request)
    {
        $name = $request->query->get('q');
        $organizationName = $request->query->get('organizationName');
        $response = [];

        if ($name && $organizationName) {
            $tags = $this->getEM()->getRepository(Production::class)->tagSuggest($name, $organizationName);

            $pagination = $this->paginator->paginate(
                $tags, /* query NOT result */
                $request->query->getInt('page', 1)/*page number*/,
                $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
            );

            /**
             * @var $production Production
             */
            foreach ($pagination->getItems() as $production) {
                $creators = '';
                $directors = '';

                foreach ($production->getCreators() as $creator) {
                    $creators .= $creator->getName() . ', ';
                }

                foreach ($production->getDirectors() as $director) {
                    $directors .= $director->getName() . ', ';
                }
                // remove the last , from the string
                $creatorsPreview = rtrim($creators, ", ");
                $directorsPreview = rtrim($directors, ", ");

                $text = sprintf('%s (%s): %s, %s: %s',
                    $production->getName(),
                    $production->getYear(),
                    $directorsPreview,
                    $this->getTranslator()->trans('people.show.detailBlock.label.directedBy'),
                    $creatorsPreview
                );

                $response[] = [
                    'id' => $production->getId(),
                    'text' => $text,
                    'total_count' => $pagination->getTotalItemCount()
                ];
            }
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/get/hidden/production", name="tj_hidden_production", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function getHiddenProductionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->query->get('idprod');
        $production = $em->getRepository("TheaterjobsProfileBundle:Production")->find($id);
        $response = array();

        $tag['year'] = $production->getYear();
        $creators = $production->getCreators();
        $directors = $production->getDirectors();
        $tag['creators'] = $tag['directors'] = array();
        foreach ($creators as $creator) {
            $crea[] = $creator->getName();
        }
        $tag['creators'][] = implode(',', $crea);
        foreach ($directors as $director) {
            $dir[] = $director->getName();
        }
        $tag['directors'][] = implode(',', $dir);
        $response[] = $tag;

        return new JsonResponse($response);
    }

}
