<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\JobHuntDone;
use Theaterjobs\AdminBundle\Form\JobHuntDoneSearchType;
use Theaterjobs\AdminBundle\Model\JobHuntDoneSearch;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * JobHuntDone controller.
 *
 * @Route("/job-hunt-done")
 */
class JobHuntDoneController extends BaseController
{

    /**
     * Lists all JobHuntDone entities.
     *
     * @Route("/index", name="admin_job_hunt_done_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $jobHuntDoneSearch = new JobHuntDoneSearch();

        $adminJobHuntDoneSearchForm = $this->createGeneralSearchForm(JobHuntDoneSearchType::class,
            $jobHuntDoneSearch,
            [],
            'admin_load_job_hunt_done_index'
        );

        return $this->render('TheaterjobsAdminBundle:JobHuntDone:index.html.twig', [
            'form' => $adminJobHuntDoneSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-job-hunt-done", name="admin_load_job_hunt_done_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadJobHunt(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $jobHuntDoneSearch = new JobHuntDoneSearch();
        $adminJobHuntDoneSearchForm = $this->createGeneralSearchForm(JobHuntDoneSearchType::class,
            $jobHuntDoneSearch,
            [],
            'admin_load_job_hunt_done_index'
        );

        $adminJobHuntDoneSearchForm->handleRequest($request);
        $adminJobHuntDoneSearch = $adminJobHuntDoneSearchForm->getData();

        $jobsHuntDone = $this->getEM()->getRepository(JobHuntDone::class)->adminListSearch($adminJobHuntDoneSearch);

        $paginator = $this->getPaginator();

        $paginatedJobsHuntDone = $paginator->paginate($jobsHuntDone, $pageNr, $rows);
        $iTotalRecords = $paginatedJobsHuntDone->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedJobsHuntDone as $jobHuntDone) {
            $nameColumn = $jobHuntDone['name'];
            $commentColumn = $jobHuntDone['comment'];
            $profileColumn = $jobHuntDone['user'];
            $createdAtColumn = isset($jobHuntDone['createdAt']) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $jobHuntDone['createdAt']])->getContent() : '';

            $records["data"][] = [
                $nameColumn,
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
