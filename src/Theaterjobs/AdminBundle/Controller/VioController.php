<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Form\VioSearchType;
use Theaterjobs\AdminBundle\Model\VioSearch;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\AdminBundle\Entity\Vio;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * Vio controller.
 *
 * @Route("/vio")
 *
 */
class VioController extends BaseController
{

    /**
     * Lists all Vio entities.
     *
     * @Route("/index", name="admin_vio_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $vioSearch = new VioSearch();

        $adminVioSearchForm = $this->createGeneralSearchForm(VioSearchType::class,
            $vioSearch,
            [],
            'tj_admin_load_vio'
        );

        return $this->render('TheaterjobsAdminBundle:Vio:index.html.twig', [
            'form' => $adminVioSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-vio", name="tj_admin_load_vio", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadVio(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $vioSearch = new VioSearch();

        $adminVioSearchForm = $this->createGeneralSearchForm(VioSearchType::class,
            $vioSearch,
            [],
            'tj_admin_load_vio'
        );

        $adminVioSearchForm->handleRequest($request);
        $adminVioSearch = $adminVioSearchForm->getData();

        $vios = $em->getRepository(Vio::class)->adminListSearch($adminVioSearch);

        $paginator = $this->get('knp_paginator');

        $paginatedVios = $paginator->paginate($vios, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedVios->getTotalItemCount();


        foreach ($paginatedVios as $vio) {
            $disable = $vio['checked'] ? 'disabled' : '';
            $actionsColumn = sprintf('<button %s class="btn btn-success" onclick="vioSave(\'%s\')">Save</button>', $disable, $vio['id']);

            $organizationColumn = sprintf('<a href=%s>%s</a>',
                $this->generateUrl('tj_organization_show',
                    [
                        'slug' => $vio['organizationSlug']
                    ]
                ),
                $vio['organization']);

            $intervalColumn = sprintf('<input id="interval-%s" %s  data-old=%s value=%s >', $vio['id'], $disable, $vio['interval'], $vio['interval']);
            $createdAtColumn = isset($vio['createdAt']) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $vio['createdAt']])->getContent() : '';

            $records["data"][] = [
                $organizationColumn,
                $intervalColumn,
                $createdAtColumn,
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
     * Creates a new Vio entity.
     *
     * @Route("/new/{slug}", name="admin_vio_create", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse
     */
    public function createAction(Request $request, Organization $organization)
    {
        $entity = new Vio();
        $em = $this->getEM();
        $entity->setOrganization($organization);
        $em->persist($entity);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true, 'id' => $entity->getId()]);
        }
    }

    /**
     * Edits an existing Vio entity.
     *
     * @Route("/update/{id}", name="admin_vio_update" , options={"expose"=true})
     * @Method("PUT")
     * @param Request $request
     * @param Vio $vio
     * @return JsonResponse
     * @internal param $id
     */
    public function updateAction(Request $request, Vio $vio)
    {
        if (!$vio) {
            throw $this->createNotFoundException('Unable to find Vio entity.');
        }

        if (!$vio->getisChecked()) {
            $interval = $request->request->getInt('interval');
            $vio->setDaysInterval($interval);
            $this->getEM()->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }

        }
    }

    /**
     * Deletes a Vio entity.
     *
     * @Route("/delete/{id}", name="admin_vio_delete", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @param Vio $vio
     * @internal param $id
     * @return JsonResponse
     *
     */
    public function deleteAction(Request $request, Vio $vio)
    {
        if (!$vio) {
            throw $this->createNotFoundException('Unable to find Vio entity.');
        }

        $this->getEM()->remove($vio);
        $this->getEM()->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
    }
}
