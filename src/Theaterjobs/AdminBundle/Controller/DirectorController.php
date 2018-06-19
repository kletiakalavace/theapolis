<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Carbon\Carbon;
use Theaterjobs\AdminBundle\Form\DirectorSearchType;
use Theaterjobs\AdminBundle\Model\DirectorSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Director;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Form\Type\DirectorType;

/**
 * Description of DirectorController
 *
 * @Route("/director")
 */
class DirectorController extends BaseController
{
    /**
     * Action to show all categories roots
     * @Route("/index", name="tj_admin_director_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $directorSearch = new DirectorSearch();

        $adminDirectorSearchForm = $this->createGeneralSearchForm(DirectorSearchType::class,
            $directorSearch,
            [],
            'admin_load_directors_index'
        );

        return $this->render('TheaterjobsAdminBundle:Director:index.html.twig', [
            'form' => $adminDirectorSearchForm->createView()
        ]);
    }

    /**
     * Lists all directors.
     *
     * @Route("/load_directors", name="admin_load_directors_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadDirectors(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $directorSearch = new DirectorSearch();


        $adminDirectorSearchForm = $this->createGeneralSearchForm(DirectorSearchType::class,
            $directorSearch,
            [],
            'admin_load_directors_index'
        );

        $adminDirectorSearchForm->handleRequest($request);
        $adminDirectorSearch = $adminDirectorSearchForm->getData();

        $directors = $this->getEM()->getRepository(Director::class)->adminListSearch($adminDirectorSearch);


        $paginator = $this->get('knp_paginator');
        $paginatedCreators = $paginator->paginate($directors, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedCreators->getTotalItemCount();

        foreach ($paginatedCreators as $director) {
            $id = $director->getId();
            $editUrl = $this->generateUrl('tj_admin_director_update', ['id' => $id]);
            $checkUrl = $this->generateUrl('tj_admin_director_check', ['id' => $id]);
            $checkLabel = 'Check';
            if ($director->getChecked()) {
                $checkUrl = $this->generateUrl('tj_admin_director_uncheck', ['id' => $id]);
                $checkLabel = 'Uncheck';
            }
            $mergeUrl = $this->generateUrl('tj_admin_director_merge_create', ['id' => $id]);
            $updatedAtColumn = $director->getUpdatedAt() ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $director->getUpdatedAt()])->getContent() : '';
            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a data-target="#myModal" data-hash="edit" data-toggle="modal"
               data-color="#244372" href=' . $editUrl . '  class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.edit') .'</a>
            <button type="button" data-url=' . $checkUrl . ' onclick="check(this)" class="btn btn-primary">' . $checkLabel . '</button>
            <a  data-target="#myModal" data-hash="merge" data-toggle="modal"
               data-color="#244372" href=' . $mergeUrl . ' class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.merge') .'</a>
           <button type="button" onclick="deleteAction(' . $id . ')" class="btn btn-primary">'. $this->getTranslator()->trans('admin.director.table.button.delete') .'</button></div>';
            $nameColumn = $director->getName();

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
     * Creates a new Director entity.
     *
     * @Route("/new", name="tj_admin_director_create")
     * @Route("/edit/{id}", name="tj_admin_director_edit")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param Director|null $director
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request, Director $director = null)
    {
        if ($director) {
            $form = $this->createEditForm(DirectorType::class,
                $director,
                [],
                'tj_admin_director_update',
                ['id' => $director->getId()]);
        } else {
            $director = new Director();
            $form = $this->createCreateForm(DirectorType::class,
                $director,
                [],
                'tj_admin_director_create'
            );
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            $repo = $this->getEM()->getRepository(Director::class);
            $formData = $form->getData();
            /** @var Director $directorCheck */
            $directorCheck = $repo->findOneByName($formData->getName());
            if ($directorCheck && ($directorCheck->getId() !== $director->getId())) {
                $productions = $director->getProductions();
                $flush = false;
                if ($productions) {
                    foreach ($productions as $production) {
                        $production->addDirectors($directorCheck);
                        $production->removeDirectors($director);
                        $this->getEM()->persist($production);
                        $flush = true;
                    }
                }
                if ($flush) {
                    $this->getEM()->flush();
                }
                if ($director->getId()) {
                    $director = $repo->find($director);
                    $repo->removeFromTree($director);
                    $this->getEM()->clear();
                }

            } else {
                $director->setChecked(true);
                $this->getEM()->persist($director);
                $this->getEM()->flush();
            }
        }
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
    }


    /**
     * Merge Director entity.
     *
     * @Route("/merge/{id}", name="tj_admin_director_merge")
     * @Method({"PUT"})
     * @param Request $request
     * @param Director|null $director
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeAction(Request $request, Director $director)
    {
        $currentName = $director->getName();
        $form = $this->createEditForm(DirectorType::class,
            $director,
            [],
            'tj_admin_director_merge',
            ['id' => $director->getId()]
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $formData = $form->getData();
            $directorCheck = $this->getEM()->getRepository(Director::class)->findOneByName($formData->getName());
            $director->setName($currentName);
            if ($directorCheck && ($directorCheck->getId() != $director->getId())) {
                $director->setParent($directorCheck);
                $director->setChecked(true);
                $this->getEM()->flush();
            }
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }
    }

    /**
     * Merge action for Director
     * @Route("/merge/{id}", name="tj_admin_director_merge_create")
     * @Method("GET")
     * @param Director|null $director
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorMergeAction(Director $director)
    {
        $form = $this->createEditForm(DirectorType::class,
            $director,
            [],
            'tj_admin_director_merge',
            ['id' => $director->getId()]
        );

        $siblingsByRoot = $this->getEM()->getRepository(Director::class)->getSiblingsByRoot($director->getRoot());

        return $this->render('TheaterjobsAdminBundle:Modal:director.html.twig', [
            'form' => $form->createView(),
            'merge' => true,
            'newCheck' => 0,
            'entity' => $director,
            'siblingByRoot' => $siblingsByRoot
        ]);
    }


    /**
     * Displays a form to create a new Director entity.
     *
     * @Route("/new", name="tj_admin_director_new")
     * @Route("/edit/{id}", name="tj_admin_director_update")
     * @Method("GET")
     * @param Director|null $director
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function directorAction(Director $director = null)
    {
        $new = 0;
        if ($director) {
            $form = $this->createEditForm(DirectorType::class,
                $director,
                [],
                'tj_admin_director_update',
                ['id' => $director->getId()]);
        } else {
            $director = new Director();
            $form = $this->createCreateForm(DirectorType::class,
                $director,
                [],
                'tj_admin_director_create'
            );
            $new = 1;
        }

        $siblingsByRoot = [];

        if ($new === 0) {
            $siblingsByRoot = $this->getEM()->getRepository(Director::class)->getSiblingsByRoot($director->getRoot());
        }

        return $this->render('TheaterjobsAdminBundle:Modal:director.html.twig', [
            'entity' => $director,
            'form' => $form->createView(),
            'merge' => null,
            'newCheck' => $new,
            'siblingByRoot' => $siblingsByRoot
        ]);
    }

    /**
     * Deletes a Director entity.
     *
     * @Route("/remove/{id}", name="tj_admin_director_delete", options={"expose"=true})
     * @param Director $director
     * @return JsonResponse
     * @internal param $id
     */
    public function deleteAction(Director $director)
    {
        if (!$director) {
            throw $this->createNotFoundException('Unable to find Director entity.');
        }

        $fosProduction = $this->container->get('fos_elastica.manager')->getRepository(Production::class);
        $productionIndex = $this->container->get('fos_elastica.index.theaterjobs.production');
        $query = $fosProduction->getDirectorProductions($director->getId());

        $productionsSearch = $productionIndex->search($query, 5);

        // check if director has productions
        if ($productionsSearch->getTotalHits() > 0) {
            return new JsonResponse ([
                'success' => false,
                'partial' => $this->render('TheaterjobsAdminBundle:Director/Partial:productions.html.twig', [
                    'productions' => $productionsSearch->getResults()
                ])->getContent()
            ]);
        }

        $this->getEM()->getRepository(Director::class)->removeFromTree($director);
        $this->getEM()->flush();
        $this->getEM()->clear();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Checks a Director entity.
     *
     * @Route("/check/{id}", name="tj_admin_director_check")
     * @param Director $director
     * @return JsonResponse
     * @internal param $id
     */
    public function checkDirectorAction(Director $director)
    {
        if (!$director) {
            throw $this->createNotFoundException('Unable to find Director entity.');
        }

        $director->setChecked(true);
        $director->setCheckedAt(Carbon::now());

        $this->getEM()->persist($director);
        $this->getEM()->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Checks a Director entity.
     *
     * @Route("/uncheck/{id}", name="tj_admin_director_uncheck")
     * @param Director $director
     * @return JsonResponse
     * @internal param $id
     */
    public function unCheckDirectorAction(Director $director)
    {
        if (!$director) {
            throw $this->createNotFoundException('Unable to find Director entity.');
        }

        $director->setChecked(false);
        $director->setCheckedAt(null);

        $this->getEM()->persist($director);
        $this->getEM()->flush();

        return new JsonResponse(['success' => true]);
    }
}
