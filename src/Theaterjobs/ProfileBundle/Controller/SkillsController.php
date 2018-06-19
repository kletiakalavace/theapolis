<?php

namespace Theaterjobs\ProfileBundle\Controller;

use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * Profile controller.
 *
 * @Route("/skills", options={"i18n" = false})
 */
class SkillsController extends BaseController
{
    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /**
     * @DI\Inject("knp_paginator")
     * @var Paginator
     */
    private $paginator;


    /**
     * @Route("/getRemoteSkills/{skillChar}", name="tj_profile_skills_index", options={"expose"=true})
     * @Method("GET")
     * @param null $skillChar
     * @return JsonResponse
     */
    public function getRemoteSkillsAction($skillChar = null)
    {

        $skills = $this->em->getRepository('TheaterjobsProfileBundle:Skill')->skillToAutosuggestion($skillChar);

        $response = array();
        foreach ($skills as $skill) {
            $array = array();
            $array['title'] = $skill->getTitle();
            $response[] = $array;
        }

        return new JsonResponse($response);

    }
    /**
     * @Route("/autosuggestion/skills", name="skills_autosuggestion", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestSkillAction(Request $request)
    {
        $em = $this->getEM();
        $title = $request->query->get('q', '');
        $language = $request->query->getBoolean('isLanguage', false);
        $newCheck = $request->query->getBoolean('isNew', false);
        $repo = $em->getRepository('TheaterjobsProfileBundle:Skill');
        $skills = $repo->getOtherSkill($title, $language);
        $results = [];

        $pagination = $this->paginator->paginate(
            $skills, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        // ids of roots that are already seen
        $rootSeen = [];
        foreach ($pagination as $node) {
            // get the root node
            $root = $node->getRoot();

            // check if root is already seen so we skip the loop
            if (!in_array($root->getId(), $rootSeen)) {
                // getting all the siblings from the root node
                $siblingsByRoot = $repo->getSiblingsByRoot($root);

                foreach ($siblingsByRoot as $sibling) {
                    $results[] = [
                        'id' => $sibling->getTitle(),
                        'text' => $sibling->getTitle(),
                        'disabled' => $newCheck,
                        'total_count' => $pagination->getTotalItemCount()
                    ];
                }
                if (!in_array($root->getId(), $rootSeen)) {
                    $rootSeen[] = $root->getId();
                }
            }
        }

        return new JsonResponse(array_values(array_unique($results, SORT_REGULAR)));
    }
}
