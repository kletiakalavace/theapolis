<?php

namespace Theaterjobs\ProfileBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\CategoryBundle\Entity\Category;
use Theaterjobs\MainBundle\Controller\BaseController;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\ProfileBundle\Entity\Experience;
use Theaterjobs\ProfileBundle\Entity\Profile;
use FOS\RestBundle\Controller\Annotations\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

/**
 * Experience controller.
 *
 * @Route("/experiences", options={"i18n" = false})
 */
class ExperienceController extends BaseController
{
    /**
     * @DI\Inject("%theaterjobs_profile.category.profile.root_slug%")
     */
    protected $jobcategoryRoot;

    /** @DI\Inject("knp_paginator") */
    private $paginator;

    /**
     * Displays a form to create a new Experience entity.
     *
     * @Route("/new", name="tj_profile_experience_new", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function newExperienceAction()
    {
        $categories = $this->getJobCategories();
        $options = [
            'category_choice_list' => $categories,
            'profile' => $this->getProfile()
        ];
        $formName = 'theaterjobs_profilebundle_experience';
        $form = $this->createCreateForm($formName, new Experience(), $options, 'tj_profile_experience_create');

        return $this->render('TheaterjobsProfileBundle:Modal/new:experience.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new Production entity.
     *
     * @Route("/create", name="tj_profile_experience_create")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createExperienceAction(Request $request)
    {
        $em = $this->getEM();
        $profile = $this->getProfile();
        $categories = $this->getJobCategories();
        $options = [
            'category_choice_list' => $categories,
            'profile' => $this->getProfile()
        ];
        $experience = new Experience();
        $formName = 'theaterjobs_profilebundle_experience';
        $form = $this->createCreateForm($formName, $experience, $options, 'tj_profile_experience_create');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $experience->setProfile($profile);
            $this->removeOldCategory($profile, $experience);
            $em->persist($experience);
            //Update profile
            $this->updateProfile($profile, false);
            $em->flush();
            return new JsonResponse($this->returnPartial($profile));
        }
        return new JsonResponse([
            'errors' => $this->getErrorMessages($form)
        ]);
    }

    /**
     * @TODO add some description
     * @param Profile $profile
     * @param Experience $experience
     * @return bool
     */
    private function removeOldCategory(Profile $profile, Experience $experience)
    {
        $em = $this->getEM();
        $oldCat = $profile->getOldCategories();
        if ($experience->getOccupation() && count($oldCat) > 0) {
            foreach ($oldCat as $categ) {
                $profile->removeOldCategory($categ);
                $em->persist($categ);
            }
            $em->persist($profile);
            $em->flush();
        }
        return true;
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
            'experiences' => $this->render('TheaterjobsProfileBundle:Partial:experiencePartial.html.twig', [
                'experiences' => $allInone['experiences'],
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
     * Displays a form to edit an existing Participation entity.
     *
     * @Route("/{id}/edit", name="tj_profile_experience_edit", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param Experience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editExperienceAction(Experience $experience)
    {
        $categories = $this->getJobCategories();
        $options = [ 'category_choice_list' => $categories ];
        $formName = "theaterjobs_profilebundle_experience";
        $editForm = $this->createEditForm($formName, $experience, $options, 'tj_profile_experience_update', ['id' => $experience->getId()]);
        $deleteForm = $this->createDeleteDeleteForm($experience);

        return $this->render('TheaterjobsProfileBundle:Modal/edit:experience.html.twig', [
            'experience' => $experience,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Updates a new Participation entity.
     *
     * @Route("/{id}/update", name="tj_profile_experience_update", condition="request.isXmlHttpRequest()")
     * @Method("PUT")
     * @param Request $request
     * @param Experience $experience
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateExperienceAction(Request $request, Experience $experience)
    {
        $em = $this->getEM();
        $profile = $this->getProfile();
        $categories = $this->getJobCategories();

        $options = [ 'category_choice_list' => $categories ];
        $formName = "theaterjobs_profilebundle_experience";
        $editForm = $this->createEditForm($formName, $experience, $options, 'tj_profile_experience_update', ['id' => $experience->getId()]);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->removeOldCategory($profile, $experience);
            //Update profile
            $this->updateProfile($profile, false);
            $em->flush();
            return new JsonResponse($this->returnPartial($profile));
        }
        return new JsonResponse([
            'errors' => $this->getErrorMessagesAJAX($editForm)
        ]);
    }

    /**
     * Creates a form to delete a Experience entity.
     *
     * @param Experience $experience
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteDeleteForm(Experience $experience)
    {
        if (!$experience) {
            throw $this->createNotFoundException('Unable to find Experience entity.');
        }

        return $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'tj_profile_experience_delete', array('id' => $experience->getId()
                    )
                )
            )
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('button.delete', array(), 'forms')))
            ->getForm();
    }

    /**
     * Deletes a Participation entity.
     *
     * @Route("/{id}/delete", name="tj_profile_experience_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Experience $experience
     * @return mixed
     */
    public function deleteParticipationAction(Request $request, Experience $experience)
    {
        $profile = $this->getProfile();
        $em = $this->getEM();
        $form = $this->createDeleteDeleteForm($experience);
        $form->handleRequest($request);
        $isAble = $this->isAbleToDeleteSection($profile);

        if ($form->isValid()) {
            if ($isAble) {
                $profile->removeExperience($experience);
                $em->remove($experience);
                //Update profile
                $this->updateProfile($profile, false);
                $em->flush();
                return new JsonResponse([
                    'success' => true,
                    'data' => $this->returnPartial($profile)
                ]);
            } else {
                $err = $this->getTranslator()->trans('profile.flash.error.unpublished.first');
                $result = [
                    'success' => false,
                    'messages' => [$err]
                ];
                return new JsonResponse($result);
            }
        }

        return new JsonResponse([
            'error' => true,
            'messages' => $this->getErrorMessagesAJAX($form)
        ]);
    }


    /**
     * Gets job categories.
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getJobCategories()
    {
        $em = $this->getEM();
        $choiceList = $em->getRepository(Category::class)->findChoiceListBySlug($this->jobcategoryRoot);
        return new ObjectChoiceList($choiceList, 'title', [], null, 'id');
    }

    /**
     *Get organizations for the autosuggested field in the form
     *
     * @Route("/organization/experience", name="tj_experience_organization", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestOrganizationsAction(Request $request)
    {
        $orgaName = $request->query->get('q');
        if (strlen($orgaName) < 3) {
            return new JsonResponse([
                'error' => true,
                'message' => $this->getTranslator()->trans('min.length.is.3')
            ]);
        }
        $organizations = $this->getRepository("TheaterjobsInserateBundle:Organization")->findLikeNameExperience($orgaName);

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

}
