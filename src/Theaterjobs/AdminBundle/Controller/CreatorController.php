<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Carbon\Carbon;
use Theaterjobs\AdminBundle\Form\CreatorSearchType;
use Theaterjobs\AdminBundle\Model\CreatorSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Creator;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Form\Type\CreatorType;


/**
 * Description of CreatorController
 *
 * @Route("/creator")
 */
class CreatorController extends BaseController
{
    /**
     * Action to show all categories roots
     * @Route("/index", name="tj_admin_creator_index")
     * @Method("GET")
     */

    public function indexAction()
    {
        $creatorSearch = new CreatorSearch();

        $adminCreatorSearchForm = $this->createGeneralSearchForm(CreatorSearchType::class,
            $creatorSearch,
            [],
            'admin_load_creators_index'
        );

        return $this->render('TheaterjobsAdminBundle:Creator:index.html.twig', [
            'form' => $adminCreatorSearchForm->createView()
        ]);
    }

    /**
     * Lists all creators.
     *
     * @Route("/load_creators", name="admin_load_creators_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadCreators(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $creatorSearch = new CreatorSearch();

        $adminCreatorSearchForm = $this->createGeneralSearchForm(CreatorSearchType::class,
            $creatorSearch,
            [],
            'admin_load_creators_index'
        );
        $adminCreatorSearchForm->handleRequest($request);
        $adminCreatorSearch = $adminCreatorSearchForm->getData();

        $creators = $this->getEM()->getRepository(Creator::class)->adminListSearch($adminCreatorSearch);
        $paginator = $this->getPaginator();

        $paginatedCreators = $paginator->paginate($creators, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedCreators->getTotalItemCount();

        foreach ($paginatedCreators as $creator) {
            $editUrl = $this->generateUrl('tj_admin_creator_update', ['id' => $creator->getId()]);
            $checkUrl = $this->generateUrl('tj_admin_creator_check', ['id' => $creator->getId()]);
            $checkLabel = 'Check';
            if ($creator->getChecked()) {
                $checkUrl = $this->generateUrl('tj_admin_creator_uncheck', ['id' => $creator->getId()]);
                $checkLabel = 'Uncheck';
            }
            $mergeUrl = $this->generateUrl('tj_admin_creator_merge_create', ['id' => $creator->getId()]);
            $updatedAtColumn = ($creator->getUpdatedAt()) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $creator->getUpdatedAt()])->getContent() : '';

            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a data-target="#myModal" data-hash="edit" data-toggle="modal"
               data-color="#244372" href=' . $editUrl . '  class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.edit') .'</a>
            <button type="button" data-url=' . $checkUrl . ' onclick="check(this)" class="btn btn-primary">' . $checkLabel . '</button>
            <a  data-target="#myModal" data-hash="merge" data-toggle="modal"
               data-color="#244372" href=' . $mergeUrl . ' class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.merge') .'</a>
           <button type="button" onclick="deleteAction(' . $creator->getId() . ')" class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.delete') .'</button></div>';

            $nameColumn = $creator->getName();
            $records["data"][] = [
                $nameColumn,
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
     * Creates a new Creator entity.
     *
     * @Route("/new", name="tj_admin_creator_create")
     * @Route("/edit/{id}", name="tj_admin_creator_edit")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param Creator|null $creator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request, Creator $creator = null)
    {

        if ($creator) {
            $form = $this->createEditForm(CreatorType::class,
                $creator,
                [],
                'tj_admin_creator_update',
                ['id' => $creator->getId()]);
        } else {
            $creator = new Creator();
            $form = $this->createCreateForm(CreatorType::class,
                $creator,
                [],
                'tj_admin_creator_create');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $repo = $this->getEM()->getRepository(Creator::class);
            $formData = $form->getData();
            $creatorCheck = $repo->findOneByName($formData->getName());
            if ($creatorCheck && ($creatorCheck->getId() !== $creator->getId())) {
                $productions = $creator->getProductions();
                if ($productions) {
                    $flush = false;
                    foreach ($productions as $production) {
                        $production->addCreators($creatorCheck);
                        $production->removeCreators($creator);
                        $this->getEM()->persist($production);
                        $flush = true;
                    }
                    if ($flush) {
                        $this->getEM()->flush();
                    }
                }

                if ($creator->getId()) {
                    $creator = $repo->find($creator);
                    $repo->removeFromTree($creator);
                    $this->getEM()->clear();
                }
            } else {
                $creator->setChecked(true);
                $this->getEM()->persist($creator);
                $this->getEM()->flush();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

    }

    /**
     * Merge Creator entity.
     *
     * @Route("/merge/{id}", name="tj_admin_creator_merge")
     * @Method({"PUT"})
     * @param Request $request
     * @param Creator|null $creator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeAction(Request $request, Creator $creator)
    {
        $currentName = $creator->getName();
        $form = $this->createEditForm(CreatorType::class,
            $creator,
            [],
            'tj_admin_creator_merge',
            ['id' => $creator->getId()]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();
            $creatorCheck = $this->getEM()->getRepository(Creator::class)->findOneByName($formData->getName());
            $creator->setName($currentName);
            if ($creatorCheck && ($creatorCheck->getId() != $creator->getId())) {
                $creator->setParent($creatorCheck);
                $creator->setChecked(true);
                $this->getEM()->flush();
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }

    }

    /**
     * Merge action for Creator
     * @Route("/merge/{id}", name="tj_admin_creator_merge_create")
     * @Method("GET")
     * @param Creator|null $creator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorMergeAction(Creator $creator)
    {
        $form = $this->createEditForm(CreatorType::class,
            $creator,
            [],
            'tj_admin_creator_merge',
            ['id' => $creator->getId()]);

        $siblingsByRoot = $this->getEM()->getRepository(Creator::class)->getSiblingsByRoot($creator->getRoot());

        return $this->render('TheaterjobsAdminBundle:Modal:creator.html.twig', [
            'form' => $form->createView(),
            'merge' => true,
            'newCheck' => 0,
            'entity' => $creator,
            'siblingByRoot' => $siblingsByRoot
        ]);
    }

    /**
     * Displays a form to create a new Creator entity.
     *
     * @Route("/new", name="tj_admin_creator_new")
     * @Route("/edit/{id}", name="tj_admin_creator_update")
     * @Method("GET")
     * @param Creator|null $creator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction(Creator $creator = null)
    {
        $new = 0;
        if ($creator) {
            $form = $this->createEditForm(CreatorType::class,
                $creator,
                [],
                'tj_admin_creator_update',
                ['id' => $creator->getId()]);
        } else {
            $creator = new Creator();
            $form = $this->createCreateForm(CreatorType::class,
                $creator,
                [],
                'tj_admin_creator_create');
            $new = 1;
        }

        $siblingsByRoot = [];

        if ($new === 0) {
            $siblingsByRoot = $this->getEM()->getRepository(Creator::class)->getSiblingsByRoot($creator->getRoot());
        }

        return $this->render('TheaterjobsAdminBundle:Modal:creator.html.twig', [
            'entity' => $creator,
            'form' => $form->createView(),
            'merge' => null,
            'newCheck' => $new,
            'siblingByRoot' => $siblingsByRoot
        ]);
    }

    /**
     * Deletes a Creator entity.
     *
     * @Route("/remove/{id}", name="tj_admin_creator_delete", options={"expose"=true})
     * @param Creator $creator
     * @return JsonResponse
     * @internal param $id
     */
    public function deleteAction(Creator $creator)
    {
        if (!$creator) {
            throw $this->createNotFoundException('Unable to find Creator entity.');
        }

        $fosProduction = $this->container->get('fos_elastica.manager')->getRepository(Production::class);
        $productionIndex = $this->container->get('fos_elastica.index.theaterjobs.production');
        $query = $fosProduction->getCreatorProductions($creator->getId());

        $productionsSearch = $productionIndex->search($query, 5);

        // check if creator has productions
        if ($productionsSearch->getTotalHits() > 0) {
            return new JsonResponse ([
                'success' => false,
                'partial' => $this->render('TheaterjobsAdminBundle:Creator/Partial:productions.html.twig', [
                    'productions' => $productionsSearch->getResults()
                ])->getContent()
            ]);
        }

        $this->getEM()->getRepository(Creator::class)->removeFromTree($creator);
        $this->getEM()->flush();
        $this->getEM()->clear();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Checks a Creator entity.
     *
     * @Route("/check/{id}", name="tj_admin_creator_check")
     * @param Creator $creator
     * @return JsonResponse
     * @internal param $id
     */
    public function checkCreatorAction(Creator $creator)
    {
        if (!$creator) {
            throw $this->createNotFoundException('Unable to find Creator entity.');
        }
        $creator->setCheckedAt(Carbon::now());
        $creator->setChecked(true);


        $this->getEM()->persist($creator);
        $this->getEM()->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Checks a Creator entity.
     *
     * @Route("/uncheck/{id}", name="tj_admin_creator_uncheck")
     * @param Creator $creator
     * @return JsonResponse
     * @internal param $id
     */
    public function unCheckCreatorAction(Creator $creator)
    {
        if (!$creator) {
            throw $this->createNotFoundException('Unable to find Creator entity.');
        }

        $creator->setCheckedAt(null);
        $creator->setChecked(false);

        $this->getEM()->persist($creator);
        $this->getEM()->flush();

        return new JsonResponse(['success' => true]);
    }

}
