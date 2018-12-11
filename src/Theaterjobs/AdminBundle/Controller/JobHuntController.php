<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\JobHunt;
use Theaterjobs\AdminBundle\Form\JobHuntSearchType;
use Theaterjobs\AdminBundle\Form\JobHuntType;
use Theaterjobs\AdminBundle\Model\JobHuntSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MainBundle\Utility\Traits\FormTrait;

/**
 * JobHunt controller.
 *
 * @Route("/job-hunt")
 */
class JobHuntController extends BaseController
{
    use FormTrait;

    /**
     * Lists all JobHunt entities.
     *
     * @Route("/index", name="admin_job_hunt_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $jobHuntSearch = new JobHuntSearch();

        $adminJobHuntSearchForm = $this->createGeneralSearchForm(JobHuntSearchType::class,
            $jobHuntSearch,
            [],
            'admin_load_job_hunt_index'
        );

        return $this->render('TheaterjobsAdminBundle:JobHunt:index.html.twig', [
            'form' => $adminJobHuntSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-job-hunt", name="admin_load_job_hunt_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadJobHunt(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $jobHuntSearch = new JobHuntSearch();

        $adminJobHuntSearchForm = $this->createGeneralSearchForm(JobHuntSearchType::class,
            $jobHuntSearch,
            [],
            'admin_load_job_hunt_index'
        );

        $adminJobHuntSearchForm->handleRequest($request);
        $adminJobHuntSearch = $adminJobHuntSearchForm->getData();

        $jobsHunt = $this->getEM()->getRepository(JobHunt::class)->adminListSearch($adminJobHuntSearch);

        $paginator = $this->getPaginator();

        $paginatedJobsHunt = $paginator->paginate($jobsHunt, $pageNr, $rows);
        $iTotalRecords = $paginatedJobsHunt->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedJobsHunt as $jobHunt) {

            $disable = $jobHunt->getisChecked() ? 'class="disabled"' : '';
            $updateUrl = $this->generateUrl('admin_job_hunt_edit', ['id' => $jobHunt->getId()]);
            $nameColumn = "<a href='$updateUrl' $disable data-target='#myModal' data-hash='edit' data-toggle='modal'
               data-color='#244372'>" . $jobHunt->getName() . "</a>";
            $url = $jobHunt->getUrl();
            $urlColumn = "<a href='$url' class='limit-length' target='_blank'>$url</a > ";
            $intervalColumn = $jobHunt->getIntervalDays() . ' days';
            $descriptionColumn = $jobHunt->getDescription();
            $createdAtColumn = $jobHunt->getCreatedAt() ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $jobHunt->getCreatedAt()])->getContent() : '';

            $records["data"][] = [
                $nameColumn,
                $urlColumn,
                $intervalColumn,
                $descriptionColumn,
                $createdAtColumn, //render orginal form in hidden because datatables sort date as text not as date type
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Creates a new JobHunt entity.
     *
     * @Route("/new", name="admin_job_hunt_new")
     * @Route("/edit/{id}", name="admin_job_hunt_update")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param JobHunt $jobHunt
     * @return JsonResponse
     */
    public function createAction(Request $request, JobHunt $jobHunt = null)
    {
        if ($jobHunt) {
            $form = $this->createEditForm(JobHuntType::class,
                $jobHunt,
                [],
                'admin_job_hunt_update',
                ['id' => $jobHunt->getId()]);
        } else {
            $jobHunt = new JobHunt();
            $form = $this->createCreateForm(JobHuntType::class,
                $jobHunt,
                [],
                'admin_job_hunt_new'
            );
        }

        $form->handleRequest($request);
        if ($form->isValid()) {

            $this->getEM()->persist($jobHunt);
            $this->getEM()->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }
        return new JsonResponse(['success' => false, 'errors' => $this->getErrorMessagesAJAX($form)]);
    }

    /**
     * Displays a form to create a new JobHunt entity.
     *
     * @Route("/new", name="admin_job_hunt_create")
     * @Route("/edit/{id}", name="admin_job_hunt_edit")
     * @Method("GET")
     * @param JobHunt $jobHunt
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function JobHuntAction(JobHunt $jobHunt = null)
    {
        $deleteForm = null;
        if ($jobHunt) {
            $form = $this->createEditForm(JobHuntType::class,
                $jobHunt,
                [],
                'admin_job_hunt_update',
                ['id' => $jobHunt->getId()]);
            $deleteForm = $this->createGeneralDeleteForm('admin_job_hunt_delete',
                ['id' => $jobHunt->getId()])->createView();
        } else {
            $jobHunt = new JobHunt();
            $form = $this->createCreateForm(JobHuntType::class,
                $jobHunt,
                [],
                'admin_job_hunt_new'
            );
        }


        return $this->render('TheaterjobsAdminBundle:Modal:jobhunt.html.twig', [
            'entity' => $jobHunt,
            'form' => $form->createView(),
            'delete' => $deleteForm

        ]);
    }

    /**
     * Deletes a JobHunt entity.
     *
     * @Route("/{id}", name="admin_job_hunt_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param JobHunt $jobHunt
     * @return JsonResponse
     */
    public function deleteAction(Request $request, JobHunt $jobHunt)
    {
        $form = $this->createGeneralDeleteForm('admin_job_hunt_delete', ['id' => $jobHunt->getId()]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$jobHunt) {
                throw $this->createNotFoundException('Unable to find JobHunt entity.');
            }

            $this->getEM()->remove($jobHunt);
            $this->getEM()->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
    }
}
