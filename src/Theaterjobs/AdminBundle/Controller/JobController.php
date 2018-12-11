<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\AdminBundle\Form\JobListType;
use Theaterjobs\AdminBundle\Form\JobRequestType;
use Theaterjobs\AdminBundle\Model\JobListSearch;
use Theaterjobs\AdminBundle\Model\JobRequestSearch;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\LastFetchedDates;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Entity\UserOrganization;

/**
 * UserOrganization controller.
 *
 * @Route("/job")
 *
 */
class JobController extends BaseController
{
    use ESUserActivity;

    /**
     * The index action.
     * @return \Symfony\Component\HttpFoundation\Response $array
     * @internal param Request $request Represents a HTTP request.
     *
     * @Route("/admin-watchlist", name="tj_main_jobs_admin_watchlist")
     */
    public function findJobsForAdminsAction()
    {
        $jobListSearch = new JobListSearch();

        $adminJobListSearchForm = $this->createGeneralSearchForm(JobListType::class,
            $jobListSearch,
            [],
            'tj_admin_load_find_jobs_for_admins'
        );

        return $this->render('TheaterjobsAdminBundle:Job:list.html.twig', [
            'form' => $adminJobListSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load_find_jobs_for_admins", name="tj_admin_load_find_jobs_for_admins" , options={"expose" = true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadFindJobsForAdminsAction(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $jobListSearch = new JobListSearch();

        $adminJobListSearchForm = $this->createGeneralSearchForm(JobListType::class,
            $jobListSearch,
            [],
            'tj_admin_load_find_jobs_for_admins'
        );

        $adminJobListSearchForm->handleRequest($request);
        $adminJobListSearch = $adminJobListSearchForm->getData();


        $watchListRecords = $this->getEM()->getRepository(Job::class)->adminListSearch($adminJobListSearch);

        $paginator = $this->getPaginator();

        $paginatedRecords = $paginator->paginate($watchListRecords, $pageNr, $rows);
        $iTotalRecords = $paginatedRecords->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedRecords as $job) {
            $userColumn = 'Missing';
            $organizationColumn = $this->getTranslator()->trans('admin.jobpublishedlist.organization.NotSet');

            if (isset($job['profileSlug'])) {
                $userColumn = '<a target="_blank" href="' . $this->generateUrl('tj_profile_profile_show', [
                        'slug' => $job['profileSlug']
                    ]) . '">' . $job['user'];
            }

            if (isset($job['organizationSlug'])) {
                $organizationColumn = '<a target="_blank" href="' . $this->generateUrl('tj_organization_show', [
                        'slug' => $job['organizationSlug']
                    ]) . '">' . $job['organization'];
            }

            // get the job status from predefined values in parameters
            $status = $this->getTranslator()->trans('admin.list.job.status.' . $this->getParameter('job_status')[$job['status']]);


            $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', [
                'date' => $job['createdAt']
            ])->getContent();

            $title = '<a  href="' . $this->generateUrl('tj_inserate_job_route_show', [
                    'slug' => $job['slug']
                ]) . '"  
                        target="_blank">' . $job['title'] . '</a>';

            $records["data"][] = [
                $title,
                $userColumn,
                $organizationColumn,
                $status,
                $date
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * The index action.
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response $array
     * @internal param Request $request Represents a HTTP request.
     *
     * @Route("/admin-checked", name="tj_main_jobs_admin_checked")
     */
    public function checkedByAdminAction(Request $request)
    {
        $profile = $this->getProfile();

        $query = $this->getEM()->getRepository(Job::class)->toBeCheckedByAdmin();

        $page = $request->query->getInt('page', 1);

        $pagination = $this->getPaginator()->paginate($query, $page);


        return $this->render('TheaterjobsAdminBundle:Job:list.html.twig', [
                'entity' => $profile,
                'watchlist' => $pagination
            ]
        );
    }

    /**
     * @Route("/other-list", name="tj_main_job_other_list")
     */
    public function listJobsToFetchAction()
    {
        return $this->render('TheaterjobsAdminBundle:Job:listjobstofetch.html.twig');
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load_job_site_reminders", name="tj_admin_load_job_site_reminders", options={"expose" = true})
     * @Method("GET")
     * @return JsonResponse
     */
    public function loadJobSiteReminders()
    {
        $xml = simplexml_load_file("http://www.vioworld.de/stellenmarkt/entry/rss/all/10/?org_openpsa_qbpager_entries_page_page=2", 'SimpleXMLElement', LIBXML_ERR_FATAL);

        $lastDate = $this->getEM()->getRepository(LastFetchedDates::class)->findOneByWebsite('vioworld');

        if (!$lastDate) {
            $lastDate = new LastFetchedDates();
            $lastDate->setWebsite("vioworld");
            $date = new \DateTime("2012-01-31");
            $lastDate->setLastDate($date);
        }

        $response = [];
        $date = strtotime($lastDate->getLastDate()->format('Y-m-d H:i:s'));
        $dateToCompare = $date + 3 * 3600; //+ 3 hrs

        foreach ($xml->channel->item as $item) {
            if (strtotime((string)$item->pubDate) > $dateToCompare) {
                $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', [
                        'date' => date("Y-m-d H:i:s", strtotime((string)$item->pubDate))
                    ]
                )->getContent();

                $response['data'][] = [
                    sprintf('<a href=% target="_blank">%s</a>', $item->link, $item->title),
                    $date
                ];
            }
        }

        return new JsonResponse($response);

    }

    /**
     * Lists all pending.
     *
     * @Route("/pending", name="tj_admin_pending_job")
     * @Method("GET")
     */
    public function pendingAction()
    {
        $jobRequestSearch = new JobRequestSearch();
        $adminJobRequestSearchForm = $this->createGeneralSearchForm(JobRequestType::class,
            $jobRequestSearch,
            [],
            'tj_admin_load_pending_job'
        );

        return $this->render('TheaterjobsAdminBundle:JobRequests:list.html.twig', [
                'form' => $adminJobRequestSearchForm->createView()
            ]
        );
    }


    /**
     * Lists all confirmed.
     *
     * @Route("/load_pending_job", name="tj_admin_load_pending_job", options={"expose" = true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadPendingJobPublications(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $jobRequestSearch = new JobRequestSearch();
        $adminJobRequestSearchForm = $this->createGeneralSearchForm(JobRequestType::class,
            $jobRequestSearch,
            [],
            'tj_admin_load_pending_job'
        );

        $adminJobRequestSearchForm->handleRequest($request);
        $adminJobRequestSearch = $adminJobRequestSearchForm->getData();
        $pendingPublications = $this->getEM()->getRepository(Job::class)->adminPendingJobRequests($adminJobRequestSearch);

        $paginator = $this->getPaginator();

        $paginatedJobPublications = $paginator->paginate(
            $pendingPublications,
            $pageNr,
            $rows
        );
        $iTotalRecords = $paginatedJobPublications->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedJobPublications as $pendingPublication) {

            $jobColumn = sprintf('<a target="_blank" href=%s>%s</a>',
                $this->generateUrl('tj_inserate_job_route_show',
                    [
                        'slug' => $pendingPublication['slug']
                    ]
                ),
                $pendingPublication['title']);

            $user_creator = 'Not Present.';



            if (isset($pendingPublication['profileSlug'])) {
                $user_creator = sprintf('<a target="_blank" href=%s>%s</a>',
                    $this->generateUrl('tj_profile_profile_show',
                        [
                            'slug' => $pendingPublication['profileSlug']
                        ]
                    ),
                    $pendingPublication['user']
                );
            }

            $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig',
                [
                    'date' => isset($pendingPublication['publishedAt']) ? $pendingPublication['publishedAt'] : $pendingPublication['requestedPublicationAt']
                ]
            )->getContent();

            $data = [
                $date,
                $user_creator,
                $jobColumn
            ];

            $records["data"][] = $data;
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Confirms a name change request.
     *
     * @Route("/confirm-pending-job-publications/{id}", name="tj_admin_confirm_pending_job_publications")
     * @Method("GET")
     * @param $id
     * @return array
     */
    public function confirmPendingPublicationAction($id)
    {
        $requestObject = $this->getEM()->getRepository('TheaterjobsInserateBundle:Inserate')->find($id);

        if (!$requestObject) {
            return ['error' => true, 'message' => 'Job could not be found.'];
        }

        $user = $requestObject->getUser();
        $profile = $user->getProfile();
        $orgasnization = $requestObject->getOrganization();
        $jobCreatorIsTeamMember = false;
        $jobCreatorWasFormerTeamMember = false;
        $messageBag = [];

        if ($orgasnization) {
            $active_users = $this->getRepository('TheaterjobsInserateBundle:Organization')->findActiveUsers($orgasnization->getId());
            foreach ($active_users as $team_member) {
                if ($team_member->getUser()->getId() == $user->getId()) {
                    $jobCreatorIsTeamMember = true;
                    break;
                }
            }

            if (!$jobCreatorIsTeamMember && !$this->isGranted('ROLE_ADMIN')) {

                $formerTeamMember = $this->getRepository('TheaterjobsInserateBundle:Organization')->findFormerMember($orgasnization->getId(), $user);

                if (count($formerTeamMember) > 0) {
                    $jobCreatorWasFormerTeamMember = true;
                }
                if ($jobCreatorWasFormerTeamMember) {
                    $newTeamMember = $formerTeamMember[0];
                    $newTeamMember->setRevokedAt(null);
                } else {
                    $newTeamMember = new UserOrganization();
                }
                $newTeamMember->setOrganization($orgasnization);
                $newTeamMember->setUser($user);
                $newTeamMember->setRequestedAt(Carbon::now());
                $newTeamMember->setGrantedAt(Carbon::now());
                $this->getEM()->persist($newTeamMember);
                $this->getEM()->flush();
                $requestObject->setStatus(1);
                $this->logUserActivity($orgasnization, $this->getTranslator()->trans('organization.activity.label.addedMember %user%', [
                    "%user%" => $profile->getFullName()], 'activity'));
                $messageBag[] = 'added_as_team_member';
            }
        }


        $requestObject->setStatus(1);
        $requestObject->setPublishedAt(Carbon::now());
        $requestObject->setRequestedPublicationAt(null);
        $requestObject->setPendingAction(null);
        $requestObject->setConfirmationToken(null);
        $this->getEM()->persist($profile);
        $this->getEM()->persist($requestObject);
        $this->getEM()->flush();

        return ['error' => false, 'messages' => $messageBag];
    }
}
