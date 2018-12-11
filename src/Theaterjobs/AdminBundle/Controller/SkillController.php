<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Form\SkillSearchType;
use Theaterjobs\AdminBundle\Model\SkillSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Skill;
use Theaterjobs\ProfileBundle\Form\Type\SkillType;

/**
 * Skill controller.
 *
 * @Route("/skills")
 */
class SkillController extends BaseController
{

    /**
     * Lists all Skill entities.
     *
     * @Route("/{type}", name="tj_admin_skill", defaults={"type": "index"}, requirements={"type"="index|languages"})
     * @Method("GET")
     * @param $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($type)
    {
        $skillSearch = new SkillSearch();
        $isLanguage = false;

        // @todo bad implementation mix skills and languages
        if ($type === 'languages') {
            $isLanguage = true;
        }

        $skillSearch->setIsLanguage($isLanguage);

        $adminSkillSearchForm = $this->createGeneralSearchForm(SkillSearchType::class,
            $skillSearch,
            [],
            'admin_load_skills_index'
        );

        return $this->render('TheaterjobsAdminBundle:Skill:index.html.twig', [
            'isLanguage' => $isLanguage,
            'form' => $adminSkillSearchForm->createView()
        ]);
    }


    /**
     * Lists all skills.
     *
     * @Route("/load-skills", name="admin_load_skills_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadSkills(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $skillSearch = new SkillSearch();

        $adminSkillSearchForm = $this->createGeneralSearchForm(SkillSearchType::class,
            $skillSearch,
            [],
            'admin_load_skills_index'
        );

        $adminSkillSearchForm->handleRequest($request);
        $adminSkillSearch = $adminSkillSearchForm->getData();

        $skills = $em->getRepository(Skill::class)->adminListSearch($adminSkillSearch);

        $paginator = $this->get('knp_paginator');

        $paginatedSkills = $paginator->paginate($skills, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedSkills->getTotalItemCount();

        foreach ($paginatedSkills as $skill) {
            $editUrl = $this->generateUrl('tj_admin_skill_edit', ['id' => $skill->getId(), 'isLanguage' => $skill->getIsLanguage()]);
            $checkUrl = $this->generateUrl('tj_admin_skill_check', ['id' => $skill->getId()]);
            $checkLabel = 'Check';

            if ($skill->getChecked()) {
                $checkUrl = $this->generateUrl('tj_admin_skill_uncheck', ['id' => $skill->getId()]);
                $checkLabel = 'Uncheck';
            }

            $mergeUrl = $this->generateUrl('tj_admin_skill_merge_create', ['id' => $skill->getId()]);
            $updatedAtColumn = ($skill->getUpdatedAt()) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $skill->getUpdatedAt()])->getContent() : '';

            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a data-target="#myModal" data-hash="edit" data-toggle="modal"
               data-color="#244372" href=' . $editUrl . ' class="btn btn-primary">Edit</a>
            <button type="button" data-url=' . $checkUrl . ' onclick="check(this)" class="btn btn-primary">' . $checkLabel . '</button>';

            if (!$skill->getIsLanguage()) {
                $actionsColumn .= '<a  data-target="#myModal" data-hash="merge" data-toggle="modal"
               data-color="#244372" href=' . $mergeUrl . ' class="btn btn-primary">Merge</a>';
            }

            $actionsColumn .= '<button type="button" onclick="deleteAction(' . $skill->getId() . ')" class="btn btn-primary">Delete</button></div>';
            $titleColumn = $skill->getTitle();
            $records["data"][] = [
                $titleColumn,
                $updatedAtColumn,
                $actionsColumn
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Creates a new Skill entity.
     *
     * @Route("/new", name="tj_admin_skill_create")
     * @Route("/edit/{id}", name="tj_admin_skill_edit")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param Skill|null $skill
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request, Skill $skill = null)
    {
        $em = $this->getEM();

        if ($skill) {
            $form = $this->createEditForm(SkillType::class,
                $skill,
                [],
                'tj_admin_skill_update',
                ['id' => $skill->getId()]);
        } else {
            $skill = new Skill();
            $skill->setInserter($this->getProfile());
            $form = $this->createCreateForm(SkillType::class,
                $skill,
                [],
                'tj_admin_skill_create');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $repo = $em->getRepository(Skill::class);
            $formData = $form->getData();
            $skillCheck = $repo->findOneByTitle($formData->getTitle());
            if ($skillCheck && ($skillCheck->getId() != $skill->getId())) {
                $sections = $skill->getSkillSection();
                if ($sections) {
                    $flush = false;
                    // get all the sections that the skill is added
                    foreach ($sections as $section) {
                        // update the  skill in the profile relation
                        $section->addProfileSkill($skillCheck);
                        $em->persist($skillCheck);
                        $flush = true;
                    }
                    if ($flush) {
                        $em->flush();
                    }
                }
                // remove the current skill
                if ($skill->getId()) {
                    $repo->removeFromTree($skill);
                    $em->clear();
                }
            } else {
                $em->persist($skill);
                $em->flush();
            }

        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

    }

    /**
     * Merge Skill entity.
     *
     * @Route("/merge/{id}", name="tj_admin_skill_merge")
     * @Method({"PUT"})
     * @param Request $request
     * @param Skill|null $skill
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeAction(Request $request, Skill $skill)
    {
        $currentTilte = $skill->getTitle();

        $em = $this->getEM();
        $form = $this->createEditForm(SkillType::class,
            $skill,
            [],
            'tj_admin_skill_merge',
            ['id' => $skill->getId()]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();
            $skillCheck = $em->getRepository(Skill::class)->findOneByTitle($formData->getTitle());
            $skill->setTitle($currentTilte);

            if ($skillCheck && ($skillCheck->getId() != $skill->getId())) {
                $skill->setParent($skillCheck);
                $skill->setChecked(true);
                $em->persist($skill);
                $em->flush();
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }

    }

    /**
     * Merge action for Skill
     * @Route("/merge/{id}", name="tj_admin_skill_merge_create")
     * @Method("GET")
     * @param Skill|null $skill
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function skillsMergeAction(Skill $skill)
    {
        $em = $this->getEM();

        $form = $this->createEditForm(SkillType::class,
            $skill,
            [],
            'tj_admin_skill_merge',
            ['id' => $skill->getId()]);

        $siblingsByRoot = $em->getRepository(Skill::class)->getSiblingsByRoot($skill->getRoot());

        return $this->render('TheaterjobsAdminBundle:Modal:skill.html.twig', [
            'form' => $form->createView(),
            'merge' => true,
            'entity' => $skill,
            'isNew' => 0,
            'isLanguage' => (int)$skill->getIsLanguage(),
            'siblingByRoot' => $siblingsByRoot
        ]);
    }

    /**
     * Displays a form to create a new Skill entity.
     *
     * @Route("/new", name="tj_admin_skill_new")
     * @Route("/edit/{id}", name="tj_admin_skill_update")
     * @Method("GET")
     * @param Request $request
     * @param Skill|null $skill
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function skillsAction(Request $request, Skill $skill = null)
    {
        $isNew = 0;
        $siblingsByRoot = [];
        $isLanguage = $request->query->getInt('isLanguage');
        $em = $this->getEM();

        if ($skill) {
            $form = $this->createEditForm(SkillType::class,
                $skill,
                [],
                'tj_admin_skill_update',
                ['id' => $skill->getId()]);
            $siblingsByRoot = $em->getRepository(Skill::class)->getSiblingsByRoot($skill->getRoot());
        } else {
            $skill = new Skill();
            $skill->setChecked(true);
            $skill->setIsLanguage($isLanguage);
            $form = $this->createCreateForm(SkillType::class,
                $skill,
                [],
                'tj_admin_skill_create');
            $isNew = 1;
        }

        return $this->render('TheaterjobsAdminBundle:Modal:skill.html.twig', [
            'entity' => $skill,
            'form' => $form->createView(),
            'merge' => null,
            'isNew' => $isNew,
            'isLanguage' => $isLanguage,
            'siblingByRoot' => $siblingsByRoot
        ]);
    }

    /**
     * Deletes a Skill entity.
     *
     * @Route("/remove/{id}", name="tj_admin_skill_delete", options={"expose"=true})
     * @param Request $request
     * @param Skill $skill
     * @return JsonResponse
     * @internal param $id
     */
    public function deleteAction(Request $request, Skill $skill)
    {
        if (!$skill) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $em = $this->getEM();
        $em->getRepository(Skill::class)->removeFromTree($skill);
        $em->clear();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
    }

    /**
     * Checks a Skill entity.
     *
     * @Route("/check/{id}", name="tj_admin_skill_check")
     * @param Request $request
     * @param Skill $skill
     * @return JsonResponse
     * @internal param $id
     */
    public function checkSkillAction(Request $request, Skill $skill)
    {
        if (!$skill) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $skill->setCheckedAt(Carbon::now());
        $skill->setChecked(true);

        $em = $this->getEM();
        $em->persist($skill);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

    }

    /**
     * Checks a Skill entity.
     *
     * @Route("/uncheck/{id}", name="tj_admin_skill_uncheck")
     * @param Request $request
     * @param Skill $skill
     * @return JsonResponse
     * @internal param $id
     */
    public function unCheckSkillAction(Request $request, Skill $skill)
    {
        $em = $this->getEM();

        if (!$skill) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $skill->setCheckedAt(null);
        $skill->setChecked(false);

        $em->persist($skill);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
    }
}
