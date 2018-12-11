<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\AdminBundle\Form\OrganizationSearchType;
use Theaterjobs\AdminBundle\Model\AdminOrganizationSearch;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\MainBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of OrganizationController
 *
 * @Route("/organization")
 */
class OrganizationController extends BaseController
{
    /**
     * Lists all confirmed.
     *
     * @Route("/load_pending_organizations", name="tj_admin_load_pending_organizations", options={"expose" = true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function loadPendingOrganizations(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $adminOrganizationSearch = new AdminOrganizationSearch();

        $adminOrganizationSearchForm = $this->createGeneralSearchForm(OrganizationSearchType::class,
            $adminOrganizationSearch,
            [],
            'tj_admin_load_pending_organizations');

        $adminOrganizationSearchForm->handleRequest($request);
        $adminOrganizationSearch = $adminOrganizationSearchForm->getData();

        $pendingOrganizations = $this->getEM()->getRepository(Organization::class)->adminListPendingOrganizationSearch($adminOrganizationSearch);
        $paginator = $this->getPaginator();

        $paginatedNameChangeRequests = $paginator->paginate($pendingOrganizations, $pageNr, $rows);
        $iTotalRecords = $paginatedNameChangeRequests->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedNameChangeRequests as $pendingOrganization) {
            $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', [
                'date' => $pendingOrganization['createdAt']
            ])->getContent();

            $organizationColumn = sprintf('<a target="_blank" href=%s>%s</a>',
                $this->generateUrl('tj_organization_show', [
                    'slug' => $pendingOrganization['slug']
                ]),
                $pendingOrganization['name']);

            $userCreator = sprintf('<a target="_blank" href=%s>%s</a>',
                $this->generateUrl('tj_profile_profile_show', [
                    'slug' => $pendingOrganization['profileSlug']
                ]),
                $pendingOrganization['email']);


            $records["data"][] = [
                $date,
                $userCreator,
                $organizationColumn
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Lists all pending.
     * @Route("/pending-organizations-list", name="tj_admin_pending_organizations")
     * @Method("GET")
     *
     */
    public function pendingAction()
    {
        $adminOrganizationSearch = new AdminOrganizationSearch();

        $adminOrganizationSearchForm = $this->createGeneralSearchForm(OrganizationSearchType::class,
            $adminOrganizationSearch,
            [],
            'tj_admin_load_pending_organizations');


        return $this->render('TheaterjobsAdminBundle:Organization:pendingOrganizations.html.twig', [
                'form' => $adminOrganizationSearchForm->createView()
            ]
        );
    }
}
