<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Form\StageTagSearchType;
use Theaterjobs\AdminBundle\Model\StageTagSearch;
use Theaterjobs\InserateBundle\Entity\OrganizationStage;
use Theaterjobs\InserateBundle\Entity\Tags;
use Theaterjobs\InserateBundle\Form\TagsType;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * Organization Stages tags controller.
 *
 * @Route("/stage-tags")
 */
class StageTagsController extends BaseController
{

    /**
     * Lists all Stage Tags entities.
     *
     * @Route("/index", name="tj_admin_stage_tags_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function indexAction()
    {
        $stageTagSearch = new StageTagSearch();

        $adminStageTagSearchForm = $this->createGeneralSearchForm(StageTagSearchType::class,
            $stageTagSearch,
            [],
            'admin_load_stage_tags_index'
        );

        return $this->render('TheaterjobsAdminBundle:StageTags:index.html.twig', [
            'form' => $adminStageTagSearchForm->createView()
        ]);
    }


    /**
     * Lists all stage tags.
     *
     * @Route("/load-stage-tags", name="admin_load_stage_tags_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function loadStageTags(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $stageTagSearch = new StageTagSearch();

        $adminStageTagSearchForm = $this->createGeneralSearchForm(StageTagSearchType::class,
            $stageTagSearch,
            [],
            'admin_load_stage_tags_index'
        );

        $adminStageTagSearchForm->handleRequest($request);
        $adminStageTagSearch = $adminStageTagSearchForm->getData();

        $tags = $em->getRepository(Tags::class)->adminListSearch($adminStageTagSearch);


        $paginator = $this->getPaginator();

        $paginatedTags = $paginator->paginate($tags, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedTags->getTotalItemCount();

        foreach ($paginatedTags as $tag) {
            $editUrl = $this->generateUrl('tj_admin_stage_tags_edit', ['id' => $tag->getId()]);
            $checkUrl = $this->generateUrl('tj_admin_stage_tags_check', ['id' => $tag->getId()]);
            $checkLabel = 'Check';

            if ($tag->getChecked()) {
                $checkUrl = $this->generateUrl('tj_admin_stage_tags_uncheck', ['id' => $tag->getId()]);
                $checkLabel = 'Uncheck';
            }

            $updatedAtColumn = ($tag->getUpdatedAt()) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $tag->getUpdatedAt()])->getContent() : '';

            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a data-target="#myModal" data-hash="edit" data-toggle="modal"
               data-color="#244372" href=' . $editUrl . '  class="btn btn-primary">Edit</a>
            <button type="button" data-url=' . $checkUrl . ' onclick="check(this)" class="btn btn-primary">' . $checkLabel . '</button>';

            $actionsColumn .= '<button type="button" onclick="deleteAction(' . $tag->getId() . ')" class="btn btn-primary">Delete</button></div>';

            $records["data"][] = [
                $tag->getTitle(),
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
     * @Route("/new", name="tj_admin_stage_tags_create")
     * @Route("/edit/{id}", name="tj_admin_stage_tags_edit")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param Tags|null $tag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function createAction(Request $request, Tags $tag = null)
    {
        $em = $this->getEM();
        if ($tag) {
            $form = $this->createEditForm(TagsType::class,
                $tag,
                [],
                'tj_admin_stage_tags_update',
                ['id' => $tag->getId()]);
        } else {
            $tag = new Tags();
            $form = $this->createCreateForm(TagsType::class,
                $tag,
                [],
                'tj_admin_stage_tags_create');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();
            $repo = $em->getRepository(Tags::class);
            $tagCheck = $repo->findOneByTitle($formData->getTitle());
            if ($tagCheck && ($tagCheck->getId() != $tag->getId())) {
                $organizationStage = $tag->getOrganizationStage();
                if ($organizationStage) {
                    $flush = false;
                    /** @var OrganizationStage $item */
                    foreach ($organizationStage as $item) {
                        $item->addTag($tagCheck);
                        $em->persist($item);
                        $flush = true;
                    }

                    if ($flush) {
                        $em->flush();
                    }
                }
                if ($tag->getId()) {
                    $em->remove($tag);
                    $em->flush();
                }
            } else {
                $tag->setChecked(true);
                $em->persist($tag);
                $em->flush();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * Displays a form to create a new Skill entity.
     *
     * @Route("/new", name="tj_admin_stage_tags_new")
     * @Route("/edit/{id}", name="tj_admin_stage_tags_update")
     * @Method("GET")
     * @param Tags|null $tag
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function tagsAction(Tags $tag = null)
    {
        $new = 0;
        if ($tag) {
            $form = $this->createEditForm(TagsType::class,
                $tag,
                [],
                'tj_admin_stage_tags_update',
                ['id' => $tag->getId()]);
        } else {
            $tag = new Tags();
            $form = $this->createCreateForm(TagsType::class,
                $tag,
                [],
                'tj_admin_stage_tags_create');
            $new = 1;
        }

        return $this->render('TheaterjobsAdminBundle:Modal:stageTag.html.twig', [
            'entity' => $tag,
            'form' => $form->createView(),
            'newCheck' => $new,
        ]);
    }

    /**
     * Deletes a Skill entity.
     *
     * @Route("/remove/{id}", name="tj_admin_stage_tags_delete", options={"expose"=true})
     * @Method("DELETE")
     * @param Request $request
     * @param Tags $tag
     * @return JsonResponse
     *
     */
    public function deleteAction(Request $request, Tags $tag)
    {
        $em = $this->getEM();

        if (!$tag) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $em->remove($tag);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * Checks a Skill entity.
     *
     * @Route("/check/{id}", name="tj_admin_stage_tags_check")
     * @param Request $request
     * @param Tags $tag
     * @return JsonResponse
     * @internal param $id
     *
     */
    public function checkSkillAction(Request $request, Tags $tag)
    {
        if (!$tag) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $tag->setCheckedAt(Carbon::now());
        $tag->setChecked(true);

        $em = $this->getEM();
        $em->persist($tag);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * Checks a Skill entity.
     *
     * @Route("/uncheck/{id}", name="tj_admin_stage_tags_uncheck")
     * @param Request $request
     * @param Tags $tag
     * @return JsonResponse
     * @internal param $id
     *
     */
    public function unCheckSkillAction(Request $request, Tags $tag)
    {
        if (!$tag) {
            throw $this->createNotFoundException('Unable to find Skill entity.');
        }

        $tag->setCheckedAt(null);
        $tag->setChecked(false);

        $em = $this->getEM();
        $em->persist($tag);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }
}
