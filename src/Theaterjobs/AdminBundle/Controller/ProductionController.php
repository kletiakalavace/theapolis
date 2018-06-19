<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\AdminBundle\Form\ProductionSearchType;
use Theaterjobs\AdminBundle\Model\ProductionSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\ProfileBundle\Entity\Production;

/**
 * Production controller.
 *
 * @Route("/productions")
 */
class ProductionController extends BaseController
{

    /**
     * Lists all Production entities.
     *
     * @Route("/index", name="tj_admin_production")
     * @Method("GET")
     */
    public function indexAction()
    {
        $productionSearch = new ProductionSearch();

        $adminProductionSearchForm = $this->createGeneralSearchForm(ProductionSearchType::class,
            $productionSearch,
            [],
            'admin_load_productions_index'
        );

        return $this->render('TheaterjobsAdminBundle:Production:index.html.twig', [
            'form' => $adminProductionSearchForm->createView()
        ]);
    }

    /**
     * Displays a form to create a new Production entity.
     *
     * @Route("/new", name="tj_admin_production_new")
     * @Method("GET")
     */
    public function newProductionAction()
    {
        $formName = "theaterjobs_profilebundle_production";
        $form = $this->createCreateForm($formName, new Production(), [], 'tj_admin_production_create');
        return $this->render('TheaterjobsProfileBundle:Production:newProd.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates a new Production entity.
     *
     * @Route("/", name="tj_admin_production_create")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createProductionAction(Request $request)
    {
        $em = $this->getEM();
        $formName = "theaterjobs_profilebundle_production";
        $production = new Production();
        $form = $this->createCreateForm($formName, $production, [], 'tj_admin_production_create');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $production = $form->getData();
            $em->persist($production);
            $em->flush();

            return $this->redirect($this->generateUrl('tj_admin_production_show', ['id' => $production->getId()]));
        }

        return $this->render('TheaterjobsProfileBundle:Production:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Production entity.
     *
     * @Route("/{id}/show", name="tj_admin_production_show")
     * @Method("GET")
     * @param $production
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Production $production)
    {
        return $this->render('TheaterjobsAdminBundle:Production:show.html.twig', [
            'entity' => $production
        ]);
    }

    /**
     * Displays a form to edit an existing Production entity.
     *
     * @Route("/{id}/edit", name="tj_admin_production_edit")
     * @Method("GET")
     * @param Production $production
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProductionAction(Production $production)
    {
        $formName = "theaterjobs_profilebundle_production";
        $editForm = $this->createEditForm($formName, $production, [], 'tj_admin_production_update', ['id' => $production->getId()]);

        return $this->render('TheaterjobsProfileBundle:Production:editProd.html.twig', [
            'production' => $production,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Edits an existing Production entity.
     *
     * @Route("/{id}", name="tj_admin_production_update")
     * @Method("PUT")
     * @param Request $request
     * @param Production $production
     * @return Response
     */
    public function updateAction(Request $request, Production $production)
    {
        $em = $this->getEM();
        $formName = "theaterjobs_profilebundle_production";
        $editForm = $this->createEditForm($formName, $production, [], 'tj_admin_production_update', ['id' => $production->getId()]);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $production = $editForm->getData();
            return $this->redirect($this->generateUrl('tj_admin_production_show', ['id' => $production->getId()]));
        }

        return $this->render('TheaterjobsProfileBundle:Production:editProd.html.twig', [
            'production' => $production,
            'edit_form' => $editForm->createView()
        ]);
    }

    /**
     * Deletes a Production entity.
     *
     * @Route("/{id}/remove", name="tj_admin_production_delete")
     * @param Production $production
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Production $production)
    {
        $em = $this->getEM();

        if (!$production->getChecked()) {
            $participations = $production->getParticipations();
            foreach ($participations as $participation) {
                $em->remove($participation);
            }
            $em->remove($production);

            $this->addFlash('productionIndex', [
                'success' => $this->getTranslator()->trans('flash.success.production.deleted')
            ]);
            $em->flush();
        } else {
            $this->addFlash('productionIndex', [
                'error' => $this->getTranslator()->trans('flash.danger.production.cannot.be.deleted')
            ]);
        }

        return $this->redirect($this->generateUrl('tj_admin_production'));
    }

    /**
     *
     * @Route("/{id}/check", name="tj_admin_production_check")
     * @Method("GET")
     * @param Production $production
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkAction(Production $production)
    {
        $em = $this->getEM();

        $production->setChecked(true);
        $production->setCheckedAt(Carbon::now());
        $production->setCheckedBy($this->getProfile());

        //check creators and directors of the production
        foreach ($production->getCreators() as $creator) {
            if (!$creator->getChecked()) {
                $creator->setChecked(true);
                $creator->setCheckedAt(Carbon::now());
            }
        }

        foreach ($production->getDirectors() as $director) {
            if (!$director->getChecked()) {
                $director->setChecked(true);
                $director->setCheckedAt(Carbon::now());
            }
        }
        $em->flush();
        return $this->redirect($this->generateUrl('tj_admin_production'));
    }

    /**
     *
     * @Route("/{id}/uncheck", name="tj_admin_production_uncheck")
     * @Method("GET")
     * @param Production $production
     * @return RedirectResponse
     */
    public function unCheckAction(Production $production)
    {
        $em = $this->getEM();

        $production->setChecked(false);
        $production->setCheckedAt(NULL);
        $production->setCheckedBy(NULL);
        $em->flush();

        $this->addFlash('productionIndex', [
            'success' => $this->get('translator')->trans('flash.success.production.unchecked')
        ]);

        return $this->redirect($this->generateUrl('tj_admin_production'));
    }

    /**
     * Lists all creators.
     *
     * @Route("/load_productions", name="admin_load_productions_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadProductions(Request $request)
    {
        $productionSearch = new ProductionSearch();

        $adminProductionSearchForm = $this->createGeneralSearchForm('admin_productions_search_type',
            $productionSearch,
            [],
            'admin_load_productions_index'
        );

        $adminProductionSearchForm->handleRequest($request);
        $adminProductionSearch = $adminProductionSearchForm->getData();

        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $fosProduction = $this->container->get('fos_elastica.manager')->getRepository(Production::class);
        $productionIndex = $this->container->get('fos_elastica.index.theaterjobs.production');
        $query = $fosProduction->search($adminProductionSearch);

        $paginator = $this->getPaginator();

        $paginatedProd = $paginator->paginate(
            new TransformedPaginatorAdapter(
                $productionIndex,
                $query, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ),
            $pageNr,
            $rows
        );

        $iTotalRecords = $paginatedProd->getTotalItemCount();

        $records = [];
        $records["data"] = [];


        foreach ($paginatedProd as $prod) {
            $creators = '';
            $directors = '';
            foreach ($prod->creators as $creator) {
                $creators .= $creator['name'] . ', ';
            }

            foreach ($prod->directors as $director) {
                $directors .= $director['name'] . ', ';
            }
            // remove the last , from the string
            $creatorsColumn = rtrim($creators, ", ");
            $directorsColumn = rtrim($directors, ", ");

            $records["data"][] = [
                $prod->name,
                $creatorsColumn,
                $directorsColumn,
                $prod->organizationRelated ? $prod->organizationRelated['name'] : '',
                $prod->year,
                $this->generateActions($prod->id, $prod->checked)
            ];
        }


        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;
        return new JsonResponse($records);

    }

    /**
     * @param $id
     * @param $action
     * @return string
     */
    public function generateActions($id, $action)
    {
        $showLabel = $this->getTranslator()->trans('admin.action.link.show');
        $showLink = $this->generateUrl('tj_admin_production_show', ['id' => $id]);
        $editLabel = $this->getTranslator()->trans('admin.action.link.edit');
        $editLink = $this->generateUrl('tj_admin_production_edit', ['id' => $id]);
        $unCheckLabel = $this->getTranslator()->trans('admin.action.link.uncheck');
        $unCheckLink = $this->generateUrl('tj_admin_production_uncheck', ['id' => $id]);
        $checkLabel = $this->getTranslator()->trans('admin.action.link.check');
        $checkLink = $this->generateUrl('tj_admin_production_check', ['id' => $id]);
        $deleteLabel = $this->getTranslator()->trans('admin.action.link.delete');
        $deleteLink = $this->generateUrl('tj_admin_production_delete', ['id' => $id]);

        // Checked Prod
        if ($action == 0) {
            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a href=' . $showLink . '  class="btn btn-primary" >' . $showLabel . '</a>
            <a data-target="#myModal" data-hash="editAdminProduction" data-toggle="modal" data-color="#87162D" 
                href=' . $editLink . '  class="btn btn-primary" >' . $editLabel . '</a>
            <a href=' . $checkLink . '  class="btn btn-primary">' . $checkLabel . '</a>
            <a href=' . $deleteLink . '  class="btn btn-primary">' . $deleteLabel . '</a>
            ';

            // Unchecked
        } else if ($action == 1) {
            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a href=' . $showLink . '  class="btn btn-primary">' . $showLabel . '</a>
            <a data-target="#myModal" data-hash="editAdminProduction" data-toggle="modal" data-color="#87162D" 
                href=' . $editLink . '  class="btn btn-primary" >' . $editLabel . '</a>
            <a href=' . $unCheckLink . '  class="btn btn-primary">' . $unCheckLabel . '</a>
            ';
            // Archived
        } else {
            $actionsColumn = '
            <div class="btn-group btn-group-sm">
            <a href=' . $showLink . '  class="btn btn-primary">' . $showLabel . '</a>
            ';
        }

        return $actionsColumn;
    }
}
