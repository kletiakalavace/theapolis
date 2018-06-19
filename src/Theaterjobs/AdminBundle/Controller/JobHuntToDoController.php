<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\JobHuntDone;
use Theaterjobs\AdminBundle\Entity\JobHuntToDo;
use Theaterjobs\AdminBundle\Form\JobHuntToDoSearchType;
use Theaterjobs\AdminBundle\Model\JobHuntToDoSearch;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * JobHuntToDo controller.
 *
 * @Route("/job-hunt-do")
 */
class JobHuntToDoController extends BaseController
{

    /**
     * Lists all JobHuntToDo entities.
     *
     * @Route("/index", name="admin_job_hunt_todo_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $jobHuntToDoSearch = new JobHuntToDoSearch();
        $adminJobHuntToDoSearchForm = $this->createGeneralSearchForm(JobHuntToDoSearchType::class,
            $jobHuntToDoSearch,
            [],
            'admin_load_job_hunt_todo_index'
        );

        return $this->render('TheaterjobsAdminBundle:JobHuntToDo:index.html.twig', [
            'form' => $adminJobHuntToDoSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-job-hunt-to-do", name="admin_load_job_hunt_todo_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadJobHunt(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $jobHuntToDoSearch = new JobHuntToDoSearch();

        $adminJobHuntToDoSearchForm = $this->createGeneralSearchForm(JobHuntToDoSearchType::class,
            $jobHuntToDoSearch,
            [],
            'admin_load_job_hunt_todo_index'
        );

        $adminJobHuntToDoSearchForm->handleRequest($request);
        $adminJobHuntToDoSearch = $adminJobHuntToDoSearchForm->getData();

        $jobsHuntTodos = $this->getEM()->getRepository(JobHuntToDo::class)->adminListSearch($adminJobHuntToDoSearch);


        $paginator = $this->getPaginator();

        $paginatedJobsHuntTodo = $paginator->paginate($jobsHuntTodos, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedJobsHuntTodo->getTotalItemCount();

        foreach ($paginatedJobsHuntTodo as $jobHuntTodo) {
            $nameColumn = '<a target="_blank" href="' . $jobHuntTodo['url'] . '"> ' . $jobHuntTodo['name'] . '</a>';
            $today = Carbon::today();
            $created = new Carbon($jobHuntTodo['createdAt']->format('Y-m-d'));
            $toSentColumn = $today->diffInDays($created) . $this->getTranslator()->trans('admin.jobtodo.daysAgo');
            $descriptionColumn = $jobHuntTodo['description'];
            $commentColumn = '<div class="form-group"><textarea id="comment-' . $jobHuntTodo['id'] . '"></textarea></div>';
            $createdAtColumn = $jobHuntTodo['createdAt'] ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted_1.html.twig', ['date' => $jobHuntTodo['createdAt']])->getContent() : '';

            $actionColumn = '<button class="btn btn-success save" id="' . $jobHuntTodo['id'] . '"> ' . $this->getTranslator()->trans('admin.jobtodo.Save') . ' </button>';
            $records["data"][] = [
                $nameColumn,
                $toSentColumn,
                $createdAtColumn,
                $descriptionColumn,
                $commentColumn,
                $actionColumn
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Creates a new JobHuntDone entity.
     *
     * @Route("/done/{id}", name="admin_job_hunt_done", options={"expose"=true})
     * @Method("POST")
     * @param JobHuntToDo $jobHuntTodo
     * @param Request $request
     * @return JsonResponse
     */
    public function doneAction(JobHuntToDo $jobHuntTodo, Request $request)
    {
        $comment = $request->request->get('comment');
        $jobHunt = $jobHuntTodo->getJobHunt();

        $jobHuntDone = new JobHuntDone();
        $jobHuntDone->setName($jobHunt->getName());
        $jobHuntDone->setProfile($this->getProfile());
        $jobHuntDone->setComment($comment);
        $jobHunt->setIschecked(false);
        $this->getEM()->persist($jobHunt);
        $this->getEM()->persist($jobHuntDone);
        $this->getEM()->remove($jobHuntTodo);
        $this->getEM()->flush();

        return new JsonResponse(['success' => true]);
    }
}
