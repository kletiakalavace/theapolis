<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\VioDone;
use Theaterjobs\AdminBundle\Form\VioDoneSearchType;
use Theaterjobs\AdminBundle\Model\VioDoneSearch;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * VioDone controller.
 *
 * @Route("/vio-done")
 *
 */
class VioDoneController extends BaseController
{

    /**
     * Lists all VioDone entities.
     *
     * @Route("/index", name="admin_vio_done_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $vioDoneSearch = new VioDoneSearch();

        $adminVioDoneSearchForm = $this->createGeneralSearchForm(VioDoneSearchType::class,
            $vioDoneSearch,
            [],
            'tj_admin_load_vio_done'
        );

        return $this->render('TheaterjobsAdminBundle:VioDone:index.html.twig', [
            'form' => $adminVioDoneSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-vio-done", name="tj_admin_load_vio_done", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadVioDone(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $vioDoneSearch = new VioDoneSearch();

        $adminVioDoneSearchForm = $this->createGeneralSearchForm(VioDoneSearchType::class,
            $vioDoneSearch,
            [],
            'tj_admin_load_vio_done'
        );

        $adminVioDoneSearchForm->handleRequest($request);
        $adminVioDoneSearch = $adminVioDoneSearchForm->getData();

        $viosDone = $em->getRepository(VioDone::class)->adminListSearch($adminVioDoneSearch);

        $paginator = $this->getPaginator();
        $paginatedViosDone = $paginator->paginate($viosDone, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedViosDone->getTotalItemCount();

        foreach ($paginatedViosDone as $vioDone) {
            $profileColumn = sprintf('<a href=%s>%s</a>',
                $this->generateUrl('tj_profile_profile_show',
                    [
                        'slug' => $vioDone['profileSlug']
                    ]
                ),
                $vioDone['user']);

            $organizationColumn = sprintf('<a href=%s>%s</a>',
                $this->generateUrl('tj_organization_show',
                    [
                        'slug' => $vioDone['organizationSlug']
                    ]
                ),
                $vioDone['organization']);

            $commentColumn = $vioDone['comment'];
            $createdAtColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', [
                'date' => $vioDone['createdAt']
            ])->getContent();

            $records["data"][] = [
                $organizationColumn,
                $profileColumn,
                $commentColumn,
                $createdAtColumn

            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }
}
