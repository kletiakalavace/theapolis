<?php

namespace Theaterjobs\InserateBundle\Controller;

use Carbon\Carbon;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Theaterjobs\InserateBundle\Entity\AdminComments;
use Theaterjobs\InserateBundle\Entity\ApplicationTrack;
use Theaterjobs\InserateBundle\Entity\Gratification;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\InserateBundle\Form\JobSearchType;
use Theaterjobs\InserateBundle\Model\JobSearch;
use Theaterjobs\InserateBundle\Security\JobVoter;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Theaterjobs\CategoryBundle\Entity\Category;

/**
 * The Job Controller.
 *
 * It provides the index action.
 *
 * @category Controller
 * @package  Theaterjobs\InserateBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @Route("/work")
 */
class JobController extends InserateController
{
    use StatisticsTrait;
    use ESUserActivity;
    use ScheduleUpdateESIndexTrait;

    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /**
     * @DI\Inject("knp_snappy.pdf")
     * @var \Knp\Snappy\GeneratorInterface
     */
    private $pdfGenerator;

    /**
     * @DI\Inject("%theaterjobs_inserate.category.job.root_slug%")
     */
    protected $jobcategoryRoot;

    /** @DI\Inject("knp_paginator") */
    private $paginator;

    /**
     * @DI\Inject("sonata.seo.page")
     * @var SeoPage
     */
    private $seo;

    /** @DI\Inject("translator") */
    private $translator;

    /**
     * Lists all Job entities.
     *
     * @return Response $array
     * @Route("/index", name="tj_inserate_job_route_home", options={"expose"=true})
     * @Method("GET")
     */
    public function indexAction()
    {
        $title = /** @Ignore */
            $this->translator->trans("default.workIndex.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = /** @Ignore */
            $this->translator->trans("default.workIndex.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = /** @Ignore */
            $this->translator->trans("default.workIndex.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $jobSearch = new JobSearch();
        $role = $this->isGranted('ROLE_ADMIN') ? 1 : 3;

        $jobSearchForm = $this->createGeneralSearchForm('job_search_type',
            $jobSearch,
            ['role' => $role],
            'tj_inserate_job_route_list'
        );

        if ($role === 1) {
            $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->getCategoriesAggregationQuery();
        } else {
            $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->searchForUser($jobSearch);
        }

        $jobAggregations = $this->get('fos_elastica.index.theaterjobs.job')->search($query)->getAggregations();

        $orderedJobAggregations = $this->orderAggSet($jobAggregations, $this->getParameter('job_base_categories'));

        return $this->render('TheaterjobsInserateBundle:Job:index.html.twig', [
                'aggs' => $orderedJobAggregations,
                'form' => $jobSearchForm->createView(),
                'showStatus' => $role
            ]
        );
    }


    /**
     * List  all job entities by search.
     *
     * @Route("/list/{category}", name="tj_inserate_job_route_list", defaults={"category" = null}, options={"expose"=true})
     * @ParamConverter("category", options={"mapping": {"category": "slug"}})
     * @Method({"GET"})
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function listAction(Request $request, Category $category = null)
    {
        $categorySlug = $category ? $category->getSlug() : $category;
        $isAjax = $request->isXmlHttpRequest();

        $jobSearch = new JobSearch();
        $role = $this->isGranted('ROLE_ADMIN') ? 1 : 3;
        $subcategories = [];


        if ($category) {
            $subcategories = $this->em->getRepository('TheaterjobsCategoryBundle:Category')->findChoiceListBySlug(
                $this->jobcategoryRoot, $categorySlug, true
            );
        }
        $jobSearchForm = $this->createGeneralSearchForm('job_search_type',
            $jobSearch,
            [
                'role' => $role,
                'subcategories' => $subcategories
            ],
            'tj_inserate_job_route_list',
            ['category' => $categorySlug]
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $jobSearch);

        $jobSearchForm->handleRequest($request);
        $jobSearch = $jobSearchForm->getData();

        $jobSearch->setCategory($category);
        $profile = $this->getProfile();
        if ($jobSearch->isFavorite()) {
            $jobSearch->setJobFavourites($profile->getJobFavouriteIds());
        }

        if ($jobSearch->isApplied()) {
            $jobSearch->setJobFavourites($profile->getJobApplicationIds());
        }

        $result = $this->container->get('fos_elastica.index.theaterjobs.job');

        $forUser = false;
        if ($this->isGranted('ROLE_ADMIN')) {
            //Shows job list of specified user
            $slug = $request->query->get('forUser');
            if ($slug) {
                $profile = $this->em->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(['slug' => $slug]);
                if ($profile) {
                    $jobSearch->setUser($profile->getUser());
                    $forUser = $slug;
                } else {
                    throw new NotFoundHttpException();
                }
            }

            $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->search($jobSearch, $subcategories);
        } else {
            $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->searchForUser($jobSearch, $subcategories);
        }

        $page = $request->query->getInt('page', 1);

        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );

        if ($category) {
            $aggs = $this->prepareAggSet($pagination->getCustomParameters(), $subcategories);

        } else {
            $aggs = $pagination->getCustomParameters();
        }

        if (isset($aggs["aggregations"]["categories"])) {
            $orderedJobAggregations = $this->orderAggSet($aggs["aggregations"], $this->container->getParameter('job_base_categories'));
            $aggs["aggregations"]["categories"]["buckets"] = $orderedJobAggregations["categories"]["buckets"];
        }


        $content = $this->render($isAjax ? 'TheaterjobsInserateBundle:Partial:jobs.html.twig' : 'TheaterjobsInserateBundle:Job:list.html.twig',
            [
                'forUser' => $forUser ? $forUser : null,
                'jobs' => $pagination,
                'aggs' => $aggs,
                'category' => $category,
                'subcategories' => $subcategories,
                'form' => $jobSearchForm->createView(),
                'showStatus' => $role
            ]
        );

        return $isAjax ? $this->generalCustomCacheControlDirective([
            'html' => $content->getContent(),
        ]) : $content;
    }

    /**
     * List  all job entities by search.
     *
     * @Route("/list-team/{category}", name="tj_inserate_job_route_list_team", defaults={"category" = null}, options={"expose"=true})
     * @ParamConverter("category", options={"mapping": {"category": "slug"}})
     * @Method({"GET"})
     * @param Request $request
     * @param Category $category
     * @return Response
     * @Security("has_role('ROLE_USER')")
     *
     */
    public function listTeamMemberAction(Request $request, Category $category = null)
    {
        $categorySlug = $category ? $category->getSlug() : $category;
        $page = $request->query->getInt('page', 1);
        $isAjax = $request->isXmlHttpRequest();

        $jobSearch = new JobSearch();
        $role = null;
        $subcategories = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $role = 1;
        } elseif ($this->getUser()->isTeamMember()) {
            $role = 4;
        }

        if ($category) {
            $subcategories = $this->em->getRepository('TheaterjobsCategoryBundle:Category')->findChoiceListBySlug(
                $this->jobcategoryRoot, $categorySlug, true
            );
        }

        $jobSearchForm = $this->createGeneralSearchForm('job_search_type',
            $jobSearch,
            [
                'isTeamList' => true,
                'role' => $role,
                'subcategories' => $subcategories
            ],
            'tj_inserate_job_route_list_team',
            ['category' => $categorySlug]
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $jobSearch);

        $jobSearchForm->handleRequest($request);
        $jobSearch->setCreateMode([Job::MODE_ORGANIZATION]);

        $jobSearch->setCategory($category);
        $profile = $this->getProfile();
        if ($jobSearch->isFavorite()) {
            $jobSearch->setJobFavourites($profile->getJobFavouriteIds());
        }

        if ($jobSearch->isApplied()) {
            $jobSearch->setJobFavourites($profile->getJobApplicationIds());
        }

        $jobSearch = $jobSearchForm->getData();

        $result = $this->container->get('fos_elastica.index.theaterjobs.job');
        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->searchForMember($jobSearch, $subcategories);


        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );

        if ($category) {
            $aggs = $this->prepareAggSet($pagination->getCustomParameters(), $subcategories);
        } else {
            $aggs = $pagination->getCustomParameters();
        }

        if (isset($aggs["aggregations"]["categories"])) {
            $orderedJobAggregations = $this->orderAggSet($aggs["aggregations"], $this->container->getParameter('job_base_categories'));
            $aggs["aggregations"]["categories"]["buckets"] = $orderedJobAggregations["categories"]["buckets"];
        }


        $content = $this->render($isAjax ? 'TheaterjobsInserateBundle:Partial:jobsTeam.html.twig' : 'TheaterjobsInserateBundle:Job:listTeam.html.twig',
            [
                'jobs' => $pagination,
                'aggs' => $aggs,
                'orgaName' => $jobSearch->getOrganization(),
                'category' => $category,
                'subcategories' => $subcategories,
                'form' => $jobSearchForm->createView()
            ]
        );

        return $isAjax ? $this->generalCustomCacheControlDirective(['html' => $content->getContent()]) : $content;
    }

    /**
     * Creates a new Job entity.
     *
     * @param Request $request Represents a HTTP request.
     *
     * @param Job $parent
     * @return Response
     *
     * @Route("/create", name="tj_inserate_job_route_create")
     * @Method("POST")
     */
    public function createAction(Request $request, Job $parent = null)
    {
        $entity = new Job();

        $entity->setUser($this->getUser());

        $form = $this->createCreateForm('tj_inserate_form_job', $entity, $this->getInserateOptions($this->jobcategoryRoot), 'tj_inserate_job_route_create');
        $form->handleRequest($request);

        $isMember = $this->isGranted('ROLE_MEMBER');
        $eduType = $form->getData()->getGratification();

        if (!$isMember && $eduType && $eduType->getTypeOf() == Gratification::TYPE_EDU) {
            $error = $this->translator->trans('job.edit.please.become.member');
            return new JsonResponse([
                'error' => true,
                'errors' => [
                    ['field' => "radio-education", 'message' => $error]
                ]
            ]);
        }

        if ($form->isValid()) {
            $organization = $form->get('organization')->getData();
            if ($organization && !$this->isGranted('ROLE_ADMIN') && !$organization->isTeamMember($this->getUser())) {
                $form->get('organization')->addError(new FormError($this->translator->trans('job.organization.you.are.not.member')));
            }

            $entity->setOrganization($organization);

            $hasOrg = $this->getRepository("TheaterjobsInserateBundle:Organization")->hasOrg($this->getUser());

            $entity->setUserHasOrg($hasOrg);

            $entity->setStatus(Inserate::STATUS_DRAFT);

            if ($this->isGranted('ROLE_ADMIN')) {
                if ($parent === null) {
                    $entity->setPublishedAt(Carbon::now());
                    $entity->setSecondCheck($this->getUser());
                }
                $entity->setFirstCheck($this->getUser());
                $entity->setNewlyPublishedJob(false);
            }

            $errors = $this->getErrorMessagesAJAX($form);

            if (count($errors) > 0) {
                return new JsonResponse(['error' => true, 'errors' => $errors]);
            } else {
                $this->em->persist($entity);
                $this->em->flush();

                $uacEvent = new UserActivityEvent($entity, $this->translator->trans("user.activity.job.created", [], 'activity'), false);
                $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

                $this->addFlash(
                    'jobShow',
                    ['success' => $this->translator->trans("flash.success.job.created", ['%jobtitle%' => $entity->getTitle()], 'flashes')]
                );
                $request->getSession()->set('myJobs', true);
                return new JsonResponse(['error' => false, 'route' => $this->generateUrl('tj_inserate_job_route_show', ['slug' => $entity->getSlug()])]);
            }
        }

        $errors = $this->getErrorMessagesAJAX($form);
        return new JsonResponse(['error' => true, 'errors' => $errors]);

    }

    /**
     * Displays a form to create a new Job entity.
     *
     * @param Request $request
     * @param Job $parent
     * @param null $orgaId
     * @return Response
     * @Route("/new/{slug}", name="tj_inserate_job_route_new_template", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @Route("/new-organization/{orgaId}", name="tj_inserate_job_route_new", defaults={"orgaId" = null}, condition="request.isXmlHttpRequest()")
     * @Route("/new-individual-job", name="tj_inserate_job_route_new_individual_job", condition="request.isXmlHttpRequest()")
     * @ParamConverter("parent", options={"mapping": {"slug": "slug"}})
     * @Method("GET")
     * @TODO check hasPeopleOrganization
     */
    public function newAction(Job $parent = null, $orgaId = null)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');

        $mode = ($routeName == 'tj_inserate_job_route_new_individual_job') ? 'individual' : 'organization';

        $entity = new Job();

        if ($parent) {
            $entity = $this->createTemplate($parent);
        }

        $form = $this->createCreateForm('tj_inserate_form_job', $entity, $this->getInserateOptions($this->jobcategoryRoot), 'tj_inserate_job_route_create');

        if ($orgaId >= 1) {
            $organization = $this->getRepository('TheaterjobsInserateBundle:Organization')->find($orgaId);
            if ($organization) {
                $form->get('organization')->setData($organization);
                if ($organization->getContactSection()) {
                    $form->get('contact')->setData($organization->getContactSection()->getContact());
                }
                if ($organization->getGeolocation()) {
                    $form->get('geolocation')->setData($organization->getGeolocation());
                }
            }
        }

        return $this->render('TheaterjobsInserateBundle:Job:new.html.twig', [
            'entity' => $entity,
            'maxCategories' => 2,
            'form' => $form->createView(),
            'mode' => $mode,
            'organization' => isset($organization) ? $organization : null,
        ]);
    }

    /**
     * Finds and displays a Job entity.
     *
     * @param Job $job
     * @return Response
     *
     * @Route("/show/{slug}", name="tj_inserate_job_route_show", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('access_job', job)")
     */
    public function showAction(Job $job)
    {
        if (!$this->isGranted(JobVoter::VIEW, $job)) {
            return $this->redirect($this->generateUrl('tj_membership_booking_new'));
        }
        $options['job'] = $job;
        $canEdit = $this->isGranted(JobVoter::EDIT, $job);
        $isAdmin = $this->isGranted(User::ROLE_ADMIN);
        $user = $this->getUser();
        $profile = $user->getProfile();
        $userOrga = $job->getUser();
        $isJobCreator = $userOrga && $user->getId() === $userOrga->getId();
        $organization = $job->getOrganization();
        $userIsTeamMember = $organization && $organization->isTeamMember($user);

        $identicalApplication = $this->em->getRepository(ApplicationTrack::class)->findIdenticalApplication($profile, $job);
        $receivingEmail = $job->getEmail();
        $canApply = !($identicalApplication || $isAdmin || !$job->isPublished() || $isJobCreator || $userIsTeamMember || !$receivingEmail);
        // Job stats
        if ($canEdit) {
            $options['allStats'] = $job->getTotalViews();
            $options['tenDaysStats'] = $this->countViewsSinceDays(Job::class, $job->getId(), 10);
            $options['activity'] = $this->getESUserActivity(Job::class, $job->getId());
        }
        //count views only if user is not an admin , not team member or job creator and only if the job is published
        if (!$canEdit && $job->isPublished()) {
            $job->setTotalViews($job->getTotalViews() + 1);
            $this->em->persist($job);
            $this->em->flush();
            // Mark entity Seen
            $this->viewEvent(Job::class, $job->getId(), $this->getUser());
        }
        // Organization data
        if ($organization) {
            $fes = $this->container->get('fos_elastica.manager');
            $newsFinder = $this->container->get('fos_elastica.index.theaterjobs.news');
            $jobIndex = $this->container->get('fos_elastica.index.theaterjobs.job');

            $queryNews = $fes->getRepository(News::class)->relatedNews($job->getOrganization()->getId());
            $queryReljobs = $fes->getRepository(Organization::class)->relatedJobs($organization->getId());
            $queryCountJobs = $fes->getRepository(Organization::class)->publishedRelatedJobs($organization->getId());

            $options['related_news'] = $newsFinder->search($queryNews)->getTotalHits();
            $options['related_jobs'] = $jobIndex->search($queryReljobs)->getAggregations();
            $options['related_jobs_count'] = $jobIndex->search($queryCountJobs)->getTotalHits();
        }
        //check if visitors profile was updated in the last 12 months.
        $profileUpdateDate = $profile->getUpdatedAt()->format('Y-m-d H:i:s');
        $visitorProfileUpdated = strtotime($profileUpdateDate) > strtotime('-365 days');

        $creatorIsAdmin = false;
        if ($job->getUser()) {
            $creatorIsAdmin = $job->getUser()->hasRole("ROLE_ADMIN");
        }
        // Admin Comments
        $adminComment = new AdminComments();
        $adminCommentForm = $this->createCreateForm('tj_admin_job_admin_comments', $adminComment, [], 'tj_admin_admin_comments_create_job');
        $adminCommentForm->get('inserate')->setData($job);

        return $this->render('@TheaterjobsInserate/Job/show.html.twig', array_merge(['commentsForm' => $adminCommentForm->createView(),
            'canEdit' => $canEdit,
            'is_team_member' => $userIsTeamMember,
            'canApply' => $canApply,
            'creatorIsAdmin' => $creatorIsAdmin,
            'applicationInfo' => $identicalApplication,
            'receivingEmail' => $receivingEmail,
            'visitorProfileUpdated' => $visitorProfileUpdated], $options));
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/all/comments/{slug}", name="tj_job_comments_all")
     * @Method({"GET"})
     * @param $slug
     * @return Response
     */
    public
    function getAllComments($slug)
    {
        $entity = $this->em->getRepository('TheaterjobsInserateBundle:Job')->findOneBy(array('slug' => $slug));

        return $this->render('TheaterjobsInserateBundle:Modal:showAllComments.html.twig', array(
            'entity' => $entity
        ));
    }


    /**
     * Changes checked by admin or add to watchlist based on input.
     *
     * @param Request $request
     * @param Job $job
     * @return Response
     *
     * @Route("/changeproperties/{slug}", name="tj_inserate_job_route_admin_watchlist", options={"expose"=true})
     * @Method("POST")
     */
    public
    function adminWatchList(Request $request, Job $job)
    {
        $option = $request->request->get('option');
        try {
            $job->setWatchList($option);
            $this->em->persist($job);
            $this->em->flush();
            $response = ['error' => false, 'message' => 'Successfully changed watchlist status', 'redirect' => $this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()])];
        } catch (\Exception $exception) {
            $response = ['error' => true, 'message' => $exception->getMessage()];

        }
        return new JsonResponse($response);
    }

    /**
     * Changes checked by admin or add to watchlist based on input.
     * @TODO check if already checked and don't do it twice
     *
     * @param Job $job
     * @return Response
     *
     * @Route("/changeChecked/{slug}", name="tj_inserate_job_route_checked_by_admin", options={"expose"=true})
     * @Method("GET")
     */
    public
    function checkedByAdmin(Job $job)
    {
        $user = $this->getUser();
        if (!$user->hasRole('ROLE_ADMIN')) {
            $this->addFlash('jobShow', ['error' => 'Permission to perform action is denied.']);
            return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
        }
        $job->setFirstCheck($user);
        $job->setNewlyPublishedJob(false);
        $activityForAdmin = ($job->getUser()->getId() == $this->getUser()->getId() ? false : true);
        $dispatcher = $this->get('event_dispatcher');

        if ($job->isPending()) {

            $adminJobController = $this->container->get('admin_job.controller');
            $result = $adminJobController->confirmPendingPublicationAction($job->getId());
            if ($result['error']) {
                $this->addFlash('jobShow', ['messages' => $result['message']]);
                return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
            } else {
                //Send notification to all members of organization
                if ($job->getOrganization()) {
                    $this->sendPublishedNotifMembers($job, true);
                    $uacEvent = new UserActivityEvent(
                        $job,
                        $this->translator->trans('user.activity.job.ApprovedAndCheckedByAdmin', [], 'activity'), $activityForAdmin
                    );
                    //Send notification only to user
                } else {
                    $this->sendPublishedNotif($job);
                    $uacEvent = new UserActivityEvent($job, $this->translator->trans('user.activity.job.ApprovedAndCheckedByAdmin', [], 'activity'), $activityForAdmin);
                }
                $msg = $this->translator->trans('user.activity.job.ApprovedAndCheckedByAdmin', [], 'activity');
                $this->addFlash('jobShow', ['success' => $msg]);
            }

        } elseif ($job->isPublished()) {

            $uacEvent = new UserActivityEvent($job, $this->translator->trans('user.activity.job.checkedByAdmin', [], 'activity'), $activityForAdmin);
            $msg = $this->translator->trans('user.activity.job.checkedByAdmin', [], 'activity');
            $this->addFlash('jobShow', ['success' => $msg]);
        }
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        $this->em->persist($job);
        $this->em->flush();

        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     * @param Job $entity
     * @param bool $iscopy
     * @return Response
     * @Route("/edit/{slug}/{iscopy}", name="tj_inserate_job_route_edit", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @Security("is_granted('edit_job', entity)")
     */
    public
    function editAction(Job $entity, $iscopy = false)
    {
        if ($iscopy) {
            return $this->copySelectedAttrJob($entity);
        } elseif ($entity->getArchivedAt() || $entity->getDestroyedAt()) {
            $error = $this->translator->trans("flash.job.archived.not.editable", ['%jobtitle%' => $entity->getTitle()], 'flashes');
            $this->addFlash('jobShow', ['danger' => $error]);
            return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $entity->getSlug()]));
        }

        $form = $this->createEditForm(
            'tj_inserate_form_job',
            $entity,
            $this->getInserateOptions($this->jobcategoryRoot),
            'tj_inserate_job_route_update',
            ['slug' => $entity->getSlug()]
        );
        $deleteForm = $this->createDeleteForm($entity->getSlug());

        return $this->render('TheaterjobsInserateBundle:Job:edit.html.twig',
            [
                'entity' => $entity,
                'maxCategories' => 2,
                'admin' => $this->isGranted('ROLE_ADMIN'),
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
                'gratificationPosition' => $entity->getGratification()->getId()
            ]
        );
    }

    /**
     * Edits an existing Job entity.
     *
     * @param Request $request Represents a HTTP request.
     * @param Job $job The job object.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Route("/update/{slug}", name="tj_inserate_job_route_update")
     * @Method("PUT")
     * @Security("is_granted('edit_job', job)")
     * @TODO Refactor
     */
    public
    function updateAction(Request $request, Job $job)
    {
        $old = clone $job;
        $oldCatId = $old->getCategories()->first()->getId();
        $opts = $this->getInserateOptions($this->jobcategoryRoot);
        $routeName = "tj_inserate_job_route_update";
        $routeOpts = ['slug' => $job->getSlug()];

        $editForm = $this->createEditForm('tj_inserate_form_job', $job, $opts, $routeName, $routeOpts);
        $editForm->handleRequest($request);

        if ($oldCatId != $job->getCategories()->first()->getId()) {
            $error = $this->translator->trans('job.edit.not.allowed.to.change.edu.type');
            $this->addFlash('jobShow',['warning' => $error]);
            return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
        }

        if ($editForm->isValid()) {

            $imagesToBeDeleted = json_decode($editForm->get('optedForDel')->getData());

            if ($imagesToBeDeleted[0]) {
                $job->setUploadFile(null);
                $job->setPath(null);
            }
            if ($imagesToBeDeleted[1]) {
                $job->setUploadFileCover(null);
                $job->setPathCover(null);
            }

            $changedValues = $this->getChangeValues($editForm, $old);

            if ($editForm->get('otherApplicationWay')->getData() && $editForm->get('contact')->getData() === null) {
                $this->addFlash(
                    'jobShow',
                    ['warning' => $this->translator->trans("flash.no.jobapplication.off.without.contacts", ['%jobtitle%' => $job->getTitle()], 'flashes')]
                );
                return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
            }

            $organization = $editForm->get('organization')->getData();

            if ($organization && !$this->isGranted('ROLE_ADMIN') && !$organization->isTeamMember($this->getUser())) {
                $error = ['field' => 'tj_inserate_form_job\[organization\]', 'message' => 'You are not a member of this organization.'];
                return new JsonResponse(['error' => true, 'errors' => [$error]]);
            }

            $job->setOrganization($organization);

            if (!$this->isGranted('ROLE_ADMIN')) {
                $job->setUpdateCounter($job->getUpdateCounter() + 1);
                if ($old->getOrganization() !== null && $job->getOrganization() === null) {
                    $job->setOrganization($old->getOrganization());
                }
                if ($job->isPublished()) {
                    $job->setTitle($old->getTitle());
                }
            }

            $gratification = $editForm->get('gratification')->getData();
            if (!$gratification) {
                $gratification = $this->getRepository("TheaterjobsInserateBundle:Gratification")->findOneBy(array('id' => 1));
                $job->setGratification($gratification);
            }

            $job->setLockFirstTimestamp(null);
            $job->setLockTimestamp(null);
            $job->setLockUser(null);

            $activictyForAdmin = ($job->getUser() == $this->getUser() ? false : true);
            $changedValues = $changedValues ? serialize($changedValues) : null;
            $log = $this->translator->trans("tj.user.activity.job.updated", [], 'activity');
            $this->logUserActivity($job, $log, $activictyForAdmin, $changedValues, null, false);
            $this->em->flush();

            $routeOpts = ['slug' => $job->getSlug()];
            return new JsonResponse(['error' => false, 'route' => $this->generateUrl('tj_inserate_job_route_show', $routeOpts)]);
        }
        $errors = $this->getErrorMessagesAJAX($editForm);
        return new JsonResponse(['error' => true, 'errors' => $errors]);
    }

    /**
     * @param $editForm
     * @param $old
     * @return array
     */
    public function getChangeValues($editForm, $old)
    {
        $changedValues = [];
        if ($editForm->get('contact')->getData() != $old->getContact()) {
            $changedValues[] = ['field' => $this->translator->trans('inserate.job.logDetails.field.contact', []), 'old' => $old->getContact(), 'new' => $editForm->get('contact')->getData()];
        }

        if ($editForm->get('title')->getData() != $old->getTitle()) {

            $changedValues[] = ['field' => $this->translator->trans('inserate.job.logDetails.field.title', []), 'old' => $old->getTitle(), 'new' => $editForm->get('title')->getData()];
        }

        if ($editForm->get('email')->getData() != $old->getEmail()) {

            $changedValues[] = ['field' => $this->translator->trans('inserate.job.logDetails.field.email', []), 'old' => $old->getEmail(), 'new' => $editForm->get('email')->getData()];
        }

        if ($editForm->get('description')->getData() != $old->getDescription()) {

            $changedValues[] = ['field' => $this->translator->trans('inserate.job.logDetails.field.description', []), 'old' => $old->getDescription(), 'new' => $editForm->get('description')->getData()];
        }
        return $changedValues;
    }

    /**
     *
     * @Route("/single/log/{id}", name="tj_log_show_single", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public
    function showSingleLogAction($id)
    {
        $activity = $this->getRepository('TheaterjobsUserBundle:UserActivity')->getSingleLog($id);

        if (!$activity) {
            throw $this->createNotFoundException('Unable to find this log.');
        }

        $template = 'TheaterjobsInserateBundle:Modal:logSingle.html.twig';

        return $this->render($template, [
                'activity' => $activity
            ]
        );

    }

    /**
     * Deletes a Job entity.
     *
     * @param Request $request Represents a HTTP request.
     * @param Job $job The job object.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/delete/{slug}", name="tj_inserate_job_route_delete")
     * @Security("is_granted('edit_job', job)")
     */
    public
    function deleteAction(Request $request, Job $job)
    {
        $form = $this->createDeleteForm($job->getSlug());
        $form->handleRequest($request);

        $activictyForAdmin = $job->getUser() !== $this->getUser();
        $job->setDestroyedAt(Carbon::now());
        $job->setStatus(Inserate::STATUS_DELETED);
        $this->em->persist($job);
        $this->em->flush();
        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($job, $this->translator->trans('tj.user.activity.job.deleted', [], 'activity'), $activictyForAdmin);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        foreach ($job->getProfileFavourites()->toArray() as $fav) {
            $job->removeProfileFavourites($fav);
            $fav->removeJobFavourite($job);
            $this->em->persist($fav);
        }
        $this->em->persist($job);
        $this->em->flush();

        $organization = $job->getOrganization();
        if ($organization) {

            $users = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')
                ->findAllUsers($organization->getId(), $this->em, [$this->getUser()->getId()]);
            if (count($users)) {
                //Send notification to users
                $title = 'dashboard.notification.job.deleted.toMembers %title% %organization% %deleter%';
                $transParams = array(
                    '%title%' => $job->getTitle(),
                    '%organization%' => $job->getOrganization()->getName(),
                    '%deleter%' => $this->getUser()->getProfile()->defaultName()
                );
                $link = 'tj_inserate_job_route_home';

                $notification = new Notification();
                $notification->setTitle($title)
                    ->setTranslationKeys($transParams)
                    ->setDescription('')
                    ->setRequireAction(false)
                    ->setLink($link);

                $notificationEvent = (new NotificationEvent())
                    ->setObjectClass(Job::class)
                    ->setObjectId($job->getId())
                    ->setNotification($notification)
                    ->setFrom($job->getUser())
                    ->setUsers($users)
                    ->setType('job_deleted');
                $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
            }
        }
        $hasOrg = $job->getOrganization();
        $this->addFlash(
            $hasOrg ? 'organizationShow' : 'jobIndex',
            ['success' => $this->translator->trans('organization.jobRemoved', [], 'flashes')]
        );
        if ($hasOrg) {
            return $this->redirect($this->generateUrl('tj_organization_show', ['slug' => $job->getOrganization()->getSlug()]));
        }
        return $this->redirect($this->generateUrl('tj_inserate_job_route_home'));
    }

    /**
     * Deletes a Job entity.
     *
     * @param Job $job The job object.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/archive/{slug}", name="tj_inserate_job_route_archive")
     * @Security("is_granted('edit_job', job)")
     */
    public
    function archiveJobAction(Job $job)
    {
        $date = new \DateTime();
        $appEnd = $job->getApplicationEnd();
        if ($appEnd !== null && $appEnd->format('Y-m-d') > $date->format('Y-m-d')) {

            $this->addFlash('jobIndex', ['messages' => $this->translator->trans(
                "flash.no.jobarchiving.before.applicationEndDate", array('%jobtitle%' => $job->getTitle()), 'flashes'
            )]);
            return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
        }
        $job->setArchivedAt(Carbon::now());
        $job->setStatus(Inserate::STATUS_ARCHIVED);
        $this->em->persist($job);
        $this->em->flush();

        $this->archivedJobViewsManager($job);


        $activictyForAdmin = ($job->getUser() == $this->getUser() ? false : true);
        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($job, $this->translator->trans('tj.user.activity.job.archived', [], 'activity'), $activictyForAdmin);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        if ($job->getOrganization()) {
            $this->sendArchivedNotifMembers($job);
        } else {
            $this->sendArchivedNotif($job);
        }
        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
    }

    /**
     * Copies a Job entity
     *
     *
     * @Route("/copy/{slug}", name="tj_inserate_job_route_copy", options={"expose"=true})
     *
     */
    public
    function copyJobAction(Job $job)
    {
        $new = $this->copySelectedAttrJob($job);
        $this->em->persist($new);
        $this->em->flush();

        return new JsonResponse([
            'slug' => $new->getSlug(),
            'categories' => $job->getCategories()
        ]);
    }

    /**
     *
     * @Route("/copy_failed", name="tj_inserate_job_route_copy_failed", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     * @internal param Job $job
     * @TODO Delete this pos
     */
    public
    function jobCopyFailedAction(Request $request)
    {
        $status = false;
        $slug = $request->request->get('slug');
        $job = $this->em->getRepository('TheaterjobsInserateBundle:Job')->findBy(['slug' => $slug]);
        $job = (isset($job[0])) ? $job[0] : false;
        foreach ($job->getCategories() as $category) {
            $job->removeCategory($category);
        }
        try {
            if (!$job) throw new \Exception();

            $this->em->remove($job);
            $this->em->flush();
            $status = true;
        } catch (\Exception $e) {
            $status = $e->getMessage();
        }

        return new JsonResponse([
            'status' => $status
        ]);
    }

    public
    function copySelectedAttrJob(Job $job)
    {

        $new = new Job();
        $new->setEmail($job->getEmail());
        $new->setStatus(Inserate::STATUS_DRAFT);
        $new->setProfile($job->getProfile());
        $new->setDescription($job->getDescription());
        $new->setContact($job->getContact());
        $new->setAdminInfoBox($job->getAdminInfoBox());
        $new->setArchivedViews($job->getArchivedViews());
        $new->setContact($job->getContact());
        $new->setEmploymentDate(null);
        $new->setEmploymentStatus(null);
        $new->setFromAge($job->getFromAge());
        $new->setGeolocation($job->getGeolocation());
        $new->setIsQueued($job->getIsQueued());
        $new->setFirstCheck(null);
        $new->setJobFromOtherSite($job->getJobFromOtherSite());
        $new->setJobFromOtherSite($job->getJobFromOtherSite());
        $new->setLockFirstTimestamp(null);
        $new->setLockTimestamp(null);
        $new->setLockUser($job->getLockUser());
        $new->setHideOrganizationLogo($job->getHideOrganizationLogo());
        $new->setOtherApplicationWay($job->getOtherApplicationWay());
        $new->setOnlyForAdmins($job->getOnlyForAdmins());
        $new->setParent($job);
        $new->setPath($job->getPath());
        $new->setPathCover($job->getPathCover());
        $new->setPlaceOfAction(null);
        $new->setToAge($job->getToAge());
        $new->setRejectDraft($job->getRejectDraft());
        $new->setUser($job->getUser());
        $new->setOrganization($job->getOrganization());
        $new->setUserHasOrg($job->getUserHasOrg());
        $new->setUpdateCounter(0);
        $new->setUploadFile($job->getUploadFile());
        $new->setUploadFileCover($job->getUploadFileCover());
        $new->setGratification($job->getGratification());
        $new->setEmploymentStatus($job->getEmploymentStatus());
        $new->setEmploymentDate($job->getEmploymentDate());
        $new->setCreatedAt(Carbon::now());
        $new->setUpdatedAt(Carbon::now());
        $new->setPublishedAt(Carbon::now());
        $new->setDestroyedAt(null);
        $new->setArchivedAt(null);

        $form = $this->createCreateForm('tj_inserate_form_job', $new, $this->getInserateOptions($this->jobcategoryRoot), 'tj_inserate_job_route_create');
        $mode = $job->getOrganization() ? 'organization' : 'individual';
        $org = $job->getOrganization();

        return $this->render('TheaterjobsInserateBundle:Job:new.html.twig', array(
            'entity' => $new,
            'maxCategories' => 2,
            'form' => $form->createView(),
            'mode' => $mode,
            'organization' => $org
        ));
    }

    /**
     * Deletes a Job entity.
     *
     * @param Job $job The job object.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/draft/{slug}", name="tj_inserate_job_route_draft")
     * @Security("is_granted('edit_job', job)")
     */
    public
    function draftJobAction(Job $job)
    {
        $job->setStatus(Inserate::STATUS_DRAFT);
        $job->setArchivedAt(null);
        $job->setDestroyedAt(null);
        $job->setPublishedAt(Carbon::now());
        $job->setWatchList(true);
        $this->em->persist($job);
        $this->em->flush();

        $this->addFlash(
            'jobShow',
            ['success' => $this->translator->trans('organization.jobUnpublished', [], 'notification')]
        );

        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
    }

    /**
     * Deletes a Job entity.
     *
     * @param Job $job The job object.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/publish/{slug}", name="tj_inserate_job_route_publish")
     * @Security("is_granted('edit_job', job)")
     */
    public
    function publishJobAction(Job $job)
    {
        $user = $this->getUser();
        $userJob = $job->getUser();

        // Check job education type limit
        $limitEduType = $this->limitEduType($job);
        if ($limitEduType) return $limitEduType;
        // Check pending requests
        $pendingRequests = $this->limitPendingJobRequests($job);
        if ($pendingRequests) return $pendingRequests;

        // Set default attr
        $job->setArchivedAt(null);
        $job->setDestroyedAt(null);
        $activityForAdmin = $userJob->getId() !== $user->getId();
        $job->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());

        //Published immediately from admin
        if ($this->isGranted('ROLE_ADMIN')) {
            //publishJobAdmin
            // Set Job Attr
            $job->setPublishedAt(Carbon::now());
            $job->setFirstCheck($user);
            $job->setPendingAction(null);
            $job->setNewlyPublishedJob(false);
            $job->setStatus(Job::STATUS_PUBLISHED);
            // Log user event
            $log = $this->translator->trans('user.activity.job.published', [], 'activity');
            // Add Flash Message
            $message = $this->translator->trans('user.notification.job.successfullypublished');
            $logMessage = [$log, $message];
        } else {
            $logMessage = $this->publishNonAdminJob($job);
        }
        // Log Activity
        $this->logUserActivity($job, $logMessage[0], $activityForAdmin, null, null, false);
        // Add Flash
        $this->addFlash('jobShow', ['success' => $logMessage[1]]);
        $this->em->flush();
        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
    }

    /**
     * Handle cases if user is not an admin
     * 1. Job has organization and orga is active and user is team member
     * 2. If job has email and needs to confirm
     * 3. If job has email and no need for confirmation
     * 4. If job has email and no need for confirmation
     * 5. If job has no email to confirm
     * @param Job $job
     * @return array
     */
    public function publishNonAdminJob(Job $job)
    {
        $now = Carbon::now();
        $user = $this->getUser();
        $jobEmail = $job->getEmail();
        $organization = $job->getOrganization();
        $mailer = $this->get('app.mailer.twig_swift');
        $needsConfirm = $jobEmail !== $user->getEmail();
        $isTeamMember = $organization && $organization->isTeamMember($user);

        if ($organization && $organization->isActive() && $isTeamMember) {
            // Set job attr
            $job->setPublishedAt($now);
            $job->setPendingAction(null);
            $job->setNewlyPublishedJob(true);
            $job->setStatus(Job::STATUS_PUBLISHED);
            // Log user event
            $log = $this->translator->trans('user.activity.job.published', [], 'activity');
            // Add Flash Message
            $message = $this->translator->trans('user.notification.job.successfullypublished');
            // Send notification to orga members
            $this->sendPublishedNotifMembers($job);

            //In pending actions
        } else {
            if ($jobEmail) {
                // Job needs Email Confirmation
                if($needsConfirm) {
                    // Set job attr
                    $job->setStatus(Job::STATUS_PENDING);
                    $job->setRequestedPublicationAt($now);
                    $job->setPendingAction(Job::WAITING_EMAIL_CONFIRM);
                    // Log user activity
                    $log = $this->translator->trans('user.activity.job.publicationRequestAwaitingEmailConfirmation', [], 'activity');
                    // Add Flash Message
                    $message = $this->translator->trans('user.activity.job.notification.requestedEmailPublication', [], 'activity');
                    // Send Email
                    $mailer->sendEmailOnJobPublish('confirmEmailOfJobForPublish', $job);
                    // Email job is eq with current user auth
                } else {
                    // Set Job attr
                    $job->setStatus(Job::STATUS_PENDING);
                    $job->setRequestedPublicationAt($now);
                    $job->setPendingAction(Job::WAITING_ADMIN_APPROVE);
                    // Log user activity
                    $log = $this->translator->trans('user.activity.job.requestedPublication', [], 'activity');
                    // Add Flash
                    $message = $this->translator->trans('user.activity.job.notification.requestedPublication', [], 'activity');
                }
                // No job email is present
            } else {
                // Set Job attr
                $job->setStatus(Job::STATUS_PENDING);
                $job->setRequestedPublicationAt($now);
                $job->setPendingAction(Job::WAITING_ADMIN_APPROVE);
                // Log User activity
                $log = $this->translator->trans('user.activity.job.requestedPublication', [], 'activity');
                // Add Flash Message
                $message = $this->translator->trans('user.activity.job.notification.requestedPublication', [], 'activity');
            }
        }
        return [$log, $message];
    }

    /**
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse | boolean
     */
    private function limitEduType(Job $job)
    {
        /** @var User $user */
        $user = $job->getUser();
        $gratification = $job->getGratification();
        if($gratification && $gratification->isEduType()) {
            if ($user->hasRole('ROLE_MEMBER') || $user->hasRole('ROLE_ADMIN')) {
                $query = $this->getESM()->getRepository(Job::class)->getPublishedEducationsByUser($user->getId(), false)->setSize(0);
                $results = $this->get('fos_elastica.index.theaterjobs.job')->search($query)->getTotalHits();
                $maxOffers = $user->getProfile()->getProfileAllowedTo()->getMaxEducationOffer();
                // Max Offers Allowed to publish
                if($results >= $maxOffers){
                    $creator = "creator.job.show.max.publication.offer.exceeded";
                    $currentUser = 'job.show.max.publication.offer.exceeded';
                    $err = $job->getId() === $this->getUser()->getId() ? $currentUser : $creator;
                    $err = $this->getTranslator()->trans($err);
                    $this->addFlash('jobShow', ['error' => $err]);
                    return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
                }
            } else {
                $creator = "error.jobCreator.is.not.member.to.publish.edu.offer";
                $currentUser = "error.please.become.member.to.publish.edu.offer";
                $err = $user->isEqual($this->getUser()) ?  $currentUser : $creator;
                $err = $this->getTranslator()->trans($err);
                $this->addFlash('jobShow', ['error' => $err]);
                return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
            }
        }
        return false;
    }

    /**
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse | false
     */
    private function limitPendingJobRequests(Job $job)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $userJob = $job->getUser();
            $query = $this->getESM()->getRepository(Job::class)->getPublishRequestsForUser($userJob->getId(), false)->setSize(0);
            $pendingJobs = $this->get('fos_elastica.index.theaterjobs.job')->search($query)->getTotalHits();

            if ($pendingJobs >= Job::PENDING_LIMIT) {
                $this->addFlash('jobShow', ['error' => $this->translator->trans('job.show.tenPending.Request')]);
                return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
            }
        }
        return false;
    }

    /**
     * Lists jobs created by User or Organizations he belongs to.
     *
     * @Route("/my-jobs/{category}", name="tj_inserate_job_route_myjobs", defaults={"category" = null}, options={"expose"=true})
     * @ParamConverter("category", options={"mapping": {"category": "slug"}})
     * @Method({"GET"})
     * @param Request $request
     * @param $category
     * @return Response
     */
    public
    function myJobsAction(Request $request, Category $category = null)
    {
        $categorySlug = $category ? $category->getSlug() : null;
        /**
         * 1 => admin
         * 3 => user
         */
        $role = 4;
        $subcategories = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $role = 1;
        }

        $userFavCheck = $request->get('favourite', 0);
        $appliedJobs = $request->get('applications', 0);

        $statusArr = $request->query->get('status') ? $request->query->get('status') : [];

        $jobSearch = new JobSearch();
        $jobSearch->setUser($this->getUser());

        if ($category) {
            $subcategories = $this->em->getRepository('TheaterjobsCategoryBundle:Category')->findChoiceListBySlug(
                $this->jobcategoryRoot, $categorySlug, true
            );
        }

        $jobSearchForm = $this->createGeneralSearchForm('job_search_type',
            $jobSearch,
            [
                'role' => $role,
                'subcategories' => $subcategories
            ],
            'tj_inserate_job_route_myjobs',
            ['category' => $categorySlug]
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $jobSearch);

        $jobSearchForm->handleRequest($request);

        if (($key = array_search(Job::STATUS_DELETED, $statusArr)) !== false) {
            unset($statusArr[$key]);
        }
        $profile = $this->getProfile();
        $jobSearch->setStatus($statusArr);
        $jobSearch->setCreateMode([Job::MODE_ORGANIZATION]);
        $jobSearch->setCategory($category);
        $jobSearch->setJobFavourites($userFavCheck == 1 ? $profile->getJobFavouriteIds() : null);
        $jobSearch->setJobApplications($appliedJobs ? $profile->getJobApplicationIds() : null);
        $jobSearch = $jobSearchForm->getData();

        $result = $this->container->get('fos_elastica.index.theaterjobs.job');
        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->searchMyJob($jobSearch, $subcategories);

        $page = $request->query->getInt('page', 1);

        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query,
                [],
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );

        if ($category) {
            $aggs = $this->prepareAggSet($pagination->getCustomParameters(), $subcategories);
        } else {
            $aggs = $pagination->getCustomParameters();
        }

        if (isset($aggs["aggregations"]["categories"])) {
            $orderedJobAggregations = $this->orderAggSet($aggs["aggregations"], $this->container->getParameter('job_base_categories'));
            $aggs["aggregations"]["categories"]["buckets"] = $orderedJobAggregations["categories"]["buckets"];
        }

        $isAjax = $request->isXmlHttpRequest();


        $content = $this->render($isAjax ? 'TheaterjobsInserateBundle:Partial:jobsMy.html.twig' : 'TheaterjobsInserateBundle:Job:listMy.html.twig',
            [
                'jobs' => $pagination,
                'aggs' => $aggs,
                'category' => $category,
                'subcategories' => $subcategories,
                'form' => $jobSearchForm->createView()
            ]

        );
        return $isAjax ? $this->generalCustomCacheControlDirective(['html' => $content->getContent()]) : $content;
    }

    /**
     * Creates a new Job based on an other Job as draft, redirects to Job create.
     * @param Job $job
     * @return Job
     */
    private function createTemplate(Job $job)
    {
        $new = new Job();
        $new->setParent($job);
        $new->setPublishedAt(null);
        $new->setArchivedAt(null);
        $new->setDestroyedAt(null);
        $new->setSlug(null);
        $new->setUser($this->getUser());

        $fields = array("Organization",
            "Gratification",
            "Occupation",
            "EngagementStart",
            "Asap",
            "HideOrganizationLogo",
            "EngagementEnd",
            "ApplicationEnd",
            "PublicationEnd",
            "Description",
            "Title");
        foreach ($fields as $field) {
            if ($job->{'get' . $field}() !== null) {
                $new->{'set' . $field}($job->{'get' . $field}());
            }
        }

        foreach ($job->getCategories() as $cat) {
            $new->addCategory($cat);
        }

        if ($job->getPlaceOfAction() !== null) {
            $new->setPlaceOfAction(clone $job->getPlaceOfAction());
        }

        if ($job->getPath() !== null) {
            $new->setPath($job->getPath());
        }


        return $new;
    }

    /**
     * @Route("/pdf/{slug}", name="tj_inserate_job_route_pdf")
     * @Method("GET")
     * @param Job $job
     * @return Response
     */
    public
    function pdfAction(Job $job)
    {
        $fs = new Filesystem();

        $jobFile = './uploads/job/' . $job->getSlug() . '.pdf';
        $fs->remove($jobFile);

        $this->pdfGenerator->setOption('encoding', 'UTF-8');
        $footerRoute = $this->generateUrl('tj_pdf_footer', [], true);

        $options = [
            'margin-top' => 10,
            'margin-right' => 0,
            'margin-bottom' => 30,
            'margin-left' => 0,
        ];
        $this->pdfGenerator->setOption('footer-html', $footerRoute);

        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->renderView('TheaterjobsInserateBundle:Job:pdf.html.twig', array(
                    'job' => $job
                )
            ), $options
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $job->getSlug() . '.pdf'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->setContent($pdf);
        return $response;
    }

    /**
     * Add job to user favourites list
     *
     * @param Job $job The job object.
     * @Route("/addjobfavourite/{slug}", name="tj_inserate_job_favourite_root", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @return JsonResponse
     */
    public
    function addJobToFavouritesAction(Job $job)
    {
        $profile = $this->getProfile();
        $status = [
            'status' => 'ERROR'
        ];

        if (!$profile->getJobFavourite()->contains($job)) {
            $profile->addJobFavourite($job);
            $this->em->flush();
            $status['status'] = "SUCCESS";
        }

        return new JsonResponse($status);
    }

    /**
     * Removes job from user favourites list
     * @param Job $job The job object.
     *
     * @Route("/removejobfavourite/{slug}", name="tj_inserate_job_favourite_remove", options={"expose"=true})
     * @Method("GET")
     * @return JsonResponse
     */
    public
    function removeFromFavouritesAction(Job $job)
    {
        $profile = $this->getProfile();
        $status = [ 'status' => 'ERROR' ];

        if ($profile->getJobFavourite()->contains($job)) {
            $profile->removeJobFavourite($job);
            $this->em->flush();
            $status['status'] = "SUCCESS";
        }
        return new JsonResponse($status);
    }

    /**
     * @Route("/get/jobtitle_typeahead", name="tj_job_get_jobtitle_typeahead", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public
    function tagSuggestTypeAheadAction(Request $request)
    {
        $queryWord = $request->query->get('q');
        $tags = $this->em->getRepository('TheaterjobsInserateBundle:JobTitle')->titleSuggest($queryWord);
        $response = [];
        foreach ($tags as $t) {
            $response[] = $t->getTitle();
        }

        return new JsonResponse(['status' => 'OK', 'data' => $response]);
    }


    /**
     * Creates a new Job entity.
     * @Route("/selectJobTYpe", name="tj_inserate_job_route_select_job_type", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public
    function selectCreateAction(Request $request)
    {
        return $this->render('TheaterjobsInserateBundle:Job:jobTypeSelect.html.twig');
    }


    /**
     * This action confirms the job by clicking the link sent to the inserted email address for this job.
     * @param $token
     * @Route("/confirmJob/{token}", name="tj_inserate_job_route_confirm_job_form_email")
     * @Method("GET")
     * @return Response
     */
    public
    function confirmAction($token)
    {
        $results = $this->em->getRepository('TheaterjobsInserateBundle:Inserate')->findInserateByConfirmationToken($token);
        if (count($results) > 0) {
            $job = $results[0];
        } else {
            return $this->render('TheaterjobsUserBundle:Registration:checkEmail.html.twig', ['state' => 'confirmationLinkBroken']);
        }


        if ($job->getStatus() == 4) {
            return $this->render('TheaterjobsUserBundle:Registration:checkEmail.html.twig', ['state' => 'confirmationLinkBroken']);
            $job->setConfirmationToken(null);
        }

        if ($job->getPendingAction() == null) {
            $this->addFlash('jobShow', ['error' => 'This job has been already been published.']);
            $job->setPendingAction(null);
        } else {
            if ($job->getOrganization()) {
                $job->setPendingAction(1);
                $this->addFlash('jobShow', ['success' => $this->translator->trans('jobpublication.notification.emailconfirm.adminReview', [], 'messages')]);
            } else {
                $job->setPendingAction(1);
                $this->addFlash('jobShow', ['success' => $this->translator->trans('jobpublication.notification.emailconfirm.adminReview', [], 'messages')]);
            }
            $job->setStatus(Job::STATUS_PENDING);
        }

        $activityForAdmin = ($job->getUser() == $this->getUser() ? false : true);
        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($job, $this->translator->trans("tj.user.activity.job.EmailConfirmedAndRequestedPublication", [], 'activity'), $activityForAdmin);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        $job->setConfirmationToken(null);

        $this->em->persist($job);
        $this->em->flush();

        if ($this->isAnon()) {
            return $this->render('TheaterjobsInserateBundle:Job:jobEmailConfirmationUserNotLogged.html.twig');
        } else {
            return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $job->getSlug()]));
        }
    }


    /**
     * Confirms a name change request.
     * @Route("/team_member_confirm_pending_job_publications/{id}", name="tj_team_member_confirm_pending_job_publications")
     * @Method("GET")
     * @param $inserate
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function confirmPendingPublicationAction(Inserate $inserate)
    {
        $user = $this->getUser();
        if (!$inserate->getOrganization()->isTeamMember($user)) {
            throw new AccessDeniedException();
        }

        $inserate->setStatus(Inserate::STATUS_PUBLISHED);
        $inserate->setPendingAction(null);
        $this->em->flush();

        $msg = $this->translator->trans('job.published.successfully');
        $this->addFlash('jobIndex', ['messages' => $msg]);

        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $inserate->getSlug()]));
    }


    /**
     * Confirms a name change request.
     * @Route("/team_member_reject_pending_job_publications/{id}", name="tj_team_member_reject_pending_job_publications")
     * @Method("GET")
     * @param $inserate
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function rejectPendingPublicationAction(Inserate $inserate)
    {
        $user = $this->getUser();
        if (!$inserate->getOrganization()->isTeamMember($user)) {
            throw new AccessDeniedException();
        }

        $inserate->setStatus(Inserate::STATUS_DRAFT);
        $inserate->setPendingAction(null);
        $inserate->setConfirmationToken(null);
        $this->em->flush();

        $msg = $this->translator->trans('job.rejected.successfully');
        $this->addFlash('jobIndex', ['messages' => $msg]);

        return $this->redirect($this->generateUrl('tj_inserate_job_route_show', ['slug' => $inserate->getSlug()]));
    }

    /**
     * Send notification to all members of organization when a job is published
     *
     * @param $job
     */
    public
    function sendPublishedNotifMembers($job, $approved = false)
    {
        $ids = $approved ? [$this->getUser()->getId()] : [];
        $users = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')
            ->findAllUsers($job->getOrganization()->getId(), $this->em, $ids);
        if (count($users)) {
            $action = $approved ? 'approved' : 'published';
            $title = "dashboard.notification.job.published.$action %title% %organization%";

            $transParams = array(
                '%title%' => $job->getTitle(),
                '%organization%' => $job->getOrganization()->getName()
            );

            $link = 'tj_inserate_job_route_show';
            $linkParams = array(
                'slug' => $job->getSlug()
            );

            $notification = new Notification();
            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setCreatedAt(Carbon::now())
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $userJob = $job->getUser();
            $notificationEvent = (new NotificationEvent())
                ->setObjectClass(User::class)
                ->setObjectId($userJob->getId())
                ->setNotification($notification)
                ->setFrom($userJob)
                ->setUsers($users)
                ->setType('job_published')
                ->setFlush(false);
            $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
        }
    }

    /**
     * Send notification to all members of organization when a job is archived
     *
     * @param $job
     */
    public
    function sendArchivedNotifMembers($job)
    {
        $users = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')
            ->findAllUsers(
                $job->getOrganization()->getId(),
                $this->em,
                [$this->getUser()->getId()]
            );

        if (count($users) > 0) {
            $title = 'dashboard.notification.job.archived.toMembers %title% %organization%';
            $transParams = array(
                '%title%' => $job->getTitle(),
                '%organization%' => $job->getOrganization()->getName()
            );

            $link = 'tj_inserate_job_route_show';
            $linkParams = array(
                'slug' => $job->getSlug()
            );

            $notification = new Notification();
            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setCreatedAt(Carbon::now())
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);


            $userJob = $job->getUser();
            $notificationEvent = (new NotificationEvent())
                ->setObjectClass(User::class)
                ->setObjectId($userJob->getId())
                ->setNotification($notification)
                ->setFrom($userJob)
                ->setUsers($users)
                ->setType('job_archived');
            $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
        }
    }

    /**
     * Send notification only to user
     *
     * @param $job
     */
    public
    function sendPublishedNotif($job)
    {
        $title = $this->translator->trans('dashboard.notification.job.published %title%', [], 'messages');

        $transParams = array('%title%' => $job->getTitle());

        $link = 'tj_inserate_job_route_show';
        $linkParams = array(
            'slug' => $job->getSlug()
        );

        $notification = new Notification();

        $notification->setTitle($title)
            ->setTranslationKeys($transParams)
            ->setDescription('')
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setLink($link)
            ->setLinkKeys($linkParams);

        $userJob = $job->getUser();
        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($userJob->getId())
            ->setNotification($notification)
            ->setFrom($userJob)
            ->setUsers($userJob)
            ->setType('job_published');

        $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
    }

    /**
     * Send notification only to user
     *
     * @param $job
     */
    public
    function sendArchivedNotif($job)
    {
        $title = 'dashboard.notification.job.archived %title%';

        $transParams = array('%title%' => $job->getTitle());

        $link = 'tj_inserate_job_route_show';
        $linkParams = array(
            'slug' => $job->getSlug()
        );

        $notification = new Notification();

        $notification->setTitle($title)
            ->setTranslationKeys($transParams)
            ->setDescription('')
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setLink($link)
            ->setLinkKeys($linkParams);

        $userJob = $job->getUser();
        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($userJob->getId())
            ->setNotification($notification)
            ->setFrom($userJob)
            ->setUsers($userJob)
            ->setType('job_archived');

        $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
    }

    /**
     * Count total views for certain job.
     *
     * @param Job $job
     */
    public
    function archivedJobViewsManager(Job $job)
    {
        $job->setArchivedViews($job->getArchivedViews() + $job->getTotalViews())->setTotalViews(0);

        $ids = $this->em->getRepository(View::class)->deleteViewsIds(Job::class, $job->getId());
        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, View::class, $ids, 'cron');

        $this->em->flush();
    }
}