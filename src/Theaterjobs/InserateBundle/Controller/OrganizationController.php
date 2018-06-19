<?php

namespace Theaterjobs\InserateBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\SeoBundle\Seo\SeoPage;
use Theaterjobs\InserateBundle\Entity\Job;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Theaterjobs\InserateBundle\Entity\AdminComments;
use Theaterjobs\InserateBundle\Entity\ContactSection;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\InserateBundle\Entity\TeamMembershipApplication;
use Theaterjobs\InserateBundle\Form\OrganizationLocationType;
use Theaterjobs\InserateBundle\Form\OrganizationLogoType;
use Theaterjobs\InserateBundle\Form\OrganizationNameType;
use Theaterjobs\InserateBundle\Form\OrganizationSearchType;
use Theaterjobs\InserateBundle\Form\OrganizationStagesType;
use Theaterjobs\InserateBundle\Form\OrganizationStatusType;
use Theaterjobs\InserateBundle\Form\OrganizationType;
use Theaterjobs\InserateBundle\Form\TeamMembershipApplicationType;
use Theaterjobs\InserateBundle\Model\OrganizationSearch;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Entity\Tags;

/**
 * The Organization Controller.
 *
 * It provides the index action.
 *
 * @category Controller
 * @package  Theaterjobs\MainBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Route("/organization")
 */
class OrganizationController extends BaseController
{
    use ESUserActivity;
    use StatisticsTrait;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @DI\Inject("knp_paginator")
     * @var Paginator
     */
    private $paginator;

    /**
     * @DI\Inject("sonata.seo.page")
     * @var SeoPage
     */
    private $seo;

    /**
     * The index action.
     *
     * @param Request $request
     * @return mixed
     * @Route("/index", name="tj_main_organization_home", options={"expose"=true})
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $title = $this->getTranslator()->trans("default.organizationIndex.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->getTranslator()->trans("default.organizationIndex.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->getTranslator()->trans("default.organizationIndex.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $anon = $this->isAnon();
        $isAjax = $request->isXmlHttpRequest();

        $organizationSearch = new OrganizationSearch();

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $forUser = false;

        if ($isAdmin) {
            $slug = $request->query->get('forUser');
            if ($slug) {
                $profile = $this->getEm()->getRepository('TheaterjobsProfileBundle:Profile')->findOneBySlug($slug);
                if ($profile) {
                    $organizationSearch->setForUser($profile->getUser());
                    $forUser = $slug;
                }
            }
        }

        $organizationSearchForm = $this->createGeneralSearchForm(OrganizationSearchType::class,
            $organizationSearch,
            [
                'role' => $isAdmin,
                'isAnon' => $anon
            ],
            'tj_main_organization_home'
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $organizationSearch);

        $organizationSearchForm->handleRequest($request);
        $organizationSearch = $organizationSearchForm->getData();

        if ($anon) {
            $organizationSearch->setFavorite(0);
            $organizationSearch->setOrganization(0);
        }

        if (!$isAdmin) {
            $organizationSearch->setDefaultStatus(true);
            // for non admin users filter list even with status 4 (closed)
            $organizationSearch->addStatus(4);
        }


        if ($organizationSearch->isFavorite()) {
            $organizationSearch->setOrganizationFavourites($this->getProfile()->getOrganizationFavouriteIds());
        }

        if ($organizationSearch->isOrganization()) {
            $organizationSearch->setMyOrganizations($this->getUser()->getUserOrganizationIds());
        }

        $result = $this->container->get('fos_elastica.index.theaterjobs.organization');
        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Organization')->search($organizationSearch);


        // Option 3b. KnpPaginator resultset
        $page = $request->query->get('page', 1);
        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );


        $content = $this->render(
            $isAjax ? 'TheaterjobsInserateBundle:Partial:organization.html.twig' : 'TheaterjobsInserateBundle:Organization:index.html.twig',
            [
                'form' => $organizationSearchForm->createView(),
                'organizations' => $pagination,
                'aggs' => $pagination->getCustomParameters(),
                'forUser' => $forUser ? $forUser : null
            ]
        );

        return $isAjax ? $this->generalCustomCacheControlDirective([
            'html' => $content->getContent()
        ]) : $content;
    }

    /**
     * Displays a form to create a new organization entity.
     * @Route("/new", name="tj_main_organization_new")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $type = OrganizationNameType::class;
        $form = $this->createCreateForm($type, new Organization(), [], 'tj_main_organization_create');
        return $this->render('TheaterjobsInserateBundle:Organization:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new Organization entity.
     *
     * @param Request $request Represents a HTTP request.
     *
     * @return JsonResponse|Response
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Route("/create", name="tj_main_organization_create")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        $organization = new Organization();
        $type = OrganizationNameType::class;
        $form = $this->createCreateForm($type, $organization, $options = [], 'tj_main_organization_create');
        $form->handleRequest($request);
        $counter = $this->getEM()->getRepository('TheaterjobsInserateBundle:Organization')->findBy(array('name' => $form->getData()->getName()));
        if (count($counter) > 0) {
            $error = new FormError($this->get('translator')->trans("organization.already.exists"));
            $form->get('name')->addError($error);
        }

        if ($form->isValid()) {

            $organization->setName($form['name']->getData());
            $organization->setUser($user);
            $this->em->persist($organization);
            $this->em->flush();

            return $this->generalCustomCacheControlDirective([
                'url' => $this->generateUrl('tj_organization_show', ['slug' => $organization->getSlug()])
            ]);
        }
        return new JsonResponse(['errors' => $this->getErrorMessagesAJAX($form)]);
    }

    /**
     * @TODO Find what this might handle
     * @param $userId
     * @return null|object|Profile
     */
    private function handleUsers($userId)
    {
        $em = $this->getEM();
        if (ctype_digit($userId)) {
            $user = $em->getRepository(Profile::class)->find($userId);
            if ($user) {
                return $user;
            }
        }
    }

    /**
     * @TODO Find what this might handle
     * @param $tag_text
     * @return null|object|Tags
     */
    private function handleTags($tag_text)
    {
        $em = $this->getEM();
        if (ctype_digit($tag_text)) {
            $tag = $em->getRepository('TheaterjobsInserateBundle:Tags')->find($tag_text);
            if ($tag) {
                return $tag;
            }
        } else {
            $tag = $em->getRepository('TheaterjobsInserateBundle:Tags')->findOneBy(array('title' => $tag_text));
            if (!$tag && strlen($tag_text) > 0) {
                $tag = new Tags();
                $tag->setTitle($tag_text);
            }
            return $tag;
        }
    }

    /**
     * Finds and displays a Job entity.
     *
     * @param Organization $organization
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route("/show/{slug}", name="tj_organization_show", options={"expose"=true})
     * @Method("GET")
     */
    public function showAction(Organization $organization)
    {
        $user = $this->getUser();
        $canEdit = $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($user);

        if (!($organization->isActiveOrClosed() || $organization->getIsVisibleInList() || $canEdit)) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $fosElastica = $this->container->get('fos_elastica.manager');
        $profileIndex = $this->container->get('fos_elastica.index.theaterjobs.profile');
        $newsIndex = $this->container->get('fos_elastica.index.theaterjobs.news');
        $newsFinder = $this->container->get('fos_elastica.finder.theaterjobs.news');
        $jobFinder = $this->container->get('fos_elastica.index.theaterjobs.job');

        $queryNews = $fosElastica->getRepository(News::class)->relatedNews($organization->getId());
        $relatedNews = $newsIndex->search($queryNews)->getTotalHits();
        $queryProfile = $fosElastica->getRepository(Organization::class)->relatedPeople($organization->getId(), $this->isGranted('ROLE_ADMIN'));
        $relatedPeople = $profileIndex->search($queryProfile)->getTotalHits();
        $queryReljobs = $fosElastica->getRepository(Organization::class)->relatedJobs($organization->getId());
        $related_jobs = $jobFinder->search($queryReljobs)->getAggregations();

        $activity = $this->getESUserActivity(Organization::class, $organization->getId());
        // Mark entity Seen
        $this->viewEvent(Organization::class, $organization->getId(), $user);

        $grants = $this->getOrderedGrants($organization);
        $listPerformance = $this->getAll($organization);

        $formName = OrganizationNameType::class;
        $routeParams = ['slug' => $organization->getSlug()];
        $nameForm = $this->createEditForm($formName, $organization, [], 'tj_main_organization_name_edit', $routeParams);

        $formName = OrganizationLogoType::class;
        $logoForm = $this->createEditForm($formName, $organization, [], 'tj_organization_update_logo', $routeParams);

        $teamMemberPendingJobPublication = $this->em->getRepository(Job::class)->getRequestsTeamMembers($organization->getId());

        $hasTeamMembershipApplication = $this->em->getRepository(TeamMembershipApplication::class)->checkIfHasUnapprovedApplications($user, $organization);

        $newsApplicationQuery = $fosElastica->getRepository(News::class)->searchApplication([
            'id' => $organization->getId(),
            'tag' => 'application'
        ]);

        $application = $newsFinder->find($newsApplicationQuery);

        $params = [
            'entity' => $organization,
            'formLogo' => $logoForm->createView(),
            'listPerformances' => $listPerformance,
            'nameForm' => $nameForm->createView(),
            'activity' => $activity,
            'application' => $application,
            'related_news' => $relatedNews,
            'related_people' => $relatedPeople,
            'grantsList' => $grants,
            'canEdit' => $canEdit,
            'related_jobs' => $related_jobs,
            'orchestra' => $organization->isOrchestra(),
            'teamMemberPendingJobPublicationRequests' => $teamMemberPendingJobPublication,
            'hasTeamMembershipApplication' => $hasTeamMembershipApplication
        ];

        if ($this->isGranted('ROLE_ADMIN')) {
            $adminCommentForm = $this->createCommentForm();
            $adminCommentForm->get('organization')->setData($organization);
            $formName = OrganizationStatusType::class;
            $routeParams = ['slug' => $organization->getSlug()];
            $statusForm = $this->createEditForm($formName, $organization, [], 'tj_organization_status', $routeParams);
            $params['commentsForm'] = $adminCommentForm->createView();
            $params['statusForm'] = $statusForm->createView();
        }

        $seoDescription = $this->get('translator')->trans("seo.organization.description", [
            '%organizationName%' => $organization->getName()
        ], 'seo');

        $this->seo->setTitle(sprintf('%s-Theapolis', $organization->getName()))
            ->addMeta('name', 'description', $seoDescription)
            ->addMeta('name', 'keywords', $this->get('translator')->trans("seo.organization.keywords", [], 'seo'));

        return $this->render('TheaterjobsInserateBundle:Organization:show.html.twig', $params);
    }

    /**
     *
     * @param $users
     * @param $activity
     * @return array
     */
    public function prepareTeamLogs($users, $activity)
    {
        if (!empty($users)) {
            foreach ($users as $k => $user) {
                $userAct = [];
                foreach ($activity as $act) {
                    // get the activity user
                    $actUser = $act->getUser();
                    if ($actUser && $actUser->getId() == $user['id']) {
                        $userAct[] = $act;
                    }
                }
                $users[$k]['activity'] = $userAct;
            }
        }
        return $users;
    }

    /**
     * @Route("/add-organization-favourite/{slug}", name="tj_organization_favourite_root", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function addOrganizationToFavouritesAction(Request $request, Organization $organization)
    {
        if ($request->isXmlHttpRequest()) {

            $authenticated_profile = $this->getUser()->getProfile();
            $status = [
                'status' => 'ERROR'
            ];

            $em = $this->getEM();

            if (!$authenticated_profile->getOrganisationFavourite()->contains($organization)) {
                $authenticated_profile->addOrganisationFavourite($organization);

                $em->persist($authenticated_profile);
                $em->flush();
                $status['status'] = "SUCCESS";
            }

            return new JsonResponse($status);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @Route("/remove-favourite/{slug}", name="tj_organization_favourite_remove", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function removeOrganizationToFavouritesAction(Request $request, Organization $organization)
    {
        if ($request->isXmlHttpRequest()) {
            $authenticated_profile = $this->getUser()->getProfile();
            $status = [
                'status' => 'ERROR'
            ];

            $em = $this->getEM();

            if ($authenticated_profile->getOrganisationFavourite()->contains($organization)) {
                $authenticated_profile->removeOrganisationFavourite($organization);

                $em->persist($authenticated_profile);
                $em->flush();
                $status['status'] = "SUCCESS";
            }

            return new JsonResponse($status);
        } else {
            throw new AccessDeniedException();
        }
    }


    /**
     * @Route("/remove-favourite-list/{slug}", name="tj_organization_favourite_list_remove")
     * @Method("GET")
     * @param Organization $organization
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Security("has_role('ROLE_USER')")
     */
    public function removeOrganizationToFavouritesListAction(Organization $organization)
    {
        $authenticated_profile = $this->getProfile();

        if ($authenticated_profile->getOrganisationFavourite()->contains($organization)) {
            $authenticated_profile->removeOrganisationFavourite($organization);

            $this->em->persist($authenticated_profile);
            $this->em->flush();
        }

        if ($authenticated_profile->getOrganisationFavourite()->count() > 0) {
            return $this->redirect($this->generateUrl('tj_main_organization_home', ['favourite' => 1]));
        } else {
            return $this->redirect($this->generateUrl('tj_main_organization_home'));
        }

    }

    /**
     * @param Organization $organization
     * @return array
     */
    private function getOrderedGrants(Organization $organization)
    {
        $grants = [];

        foreach ($organization->getOrganizationGrants() as $grant) {
            $firstSeason = strtok($grant->getSeason(), '/');
            $grants[$firstSeason] = $grant;
        }

        if ($grants) {
            krsort($grants);
        }

        return $grants;
    }

    /**
     * Creates a form to add comment on organization.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCommentForm()
    {
        $entity = new AdminComments();
        $form = $this->createForm('tj_admin_admin_comments_create_orga', $entity, array(
            'action' => $this->generateUrl('tj_admin_admin_comments_create_orga'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->getTranslator()->trans('button.comment')));

        return $form;
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/all/comments/{slug}", name="tj_organization_comments_all")
     * @Method({"GET"})
     * @param $slug
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function getAllComments($slug)
    {
        $em = $this->getEM();
        $entity = $em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('slug' => $slug));

        return $this->render('TheaterjobsInserateBundle:Modal:showAllComments.html.twig', array(
            'entity' => $entity
        ));
    }

    /**
     *
     * @Route("/edit/name/{slug}", name="tj_main_organization_name_edit", defaults={"slug" = null})
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Security("has_role('ROLE_USER')")
     */
    public function editNameAction(Request $request, Organization $organization)
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($user)) {

            $oldName = $organization->getName();
            if (!$organization) {
                throw $this->createNotFoundException('Unable to find Organization entity.');
            }

            $type = OrganizationNameType::class;
            $routeParams = ['slug' => $organization->getSlug()];
            $editForm = $this->createEditForm($type, $organization, [], 'tj_main_organization_name_edit', $routeParams);
            $editForm->handleRequest($request);


            $counter = $this->getEM()->getRepository('TheaterjobsInserateBundle:Organization')->findBy(array('name' => $editForm->getData()->getName()));
            if ($counter) {
                $error = new FormError($this->get('translator')->trans("organization.already.exists"));
                $editForm->get('name')->addError($error);
            }
            if ($editForm->isValid()) {
                // Find changes
                $organization->setUpdatedAt(new \DateTime());
                $this->em->persist($organization);
                $this->em->flush();
                $helper[] = ['field' => $this->get('translator')->trans('inserate.organization.logDetails.field.name', []), 'old' => $oldName, 'new' => $editForm->get('name')->getData()];

                $dispatcher = $this->get('event_dispatcher');
                $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.UpdatedorganizationName', [], 'activity'), false, serialize($helper));
                $dispatcher->dispatch("UserActivityEvent", $uacEvent);

                if ($request->isXmlHttpRequest()) {
                    $fosElastica = $this->container->get('fos_elastica.manager');
                    $profileIndex = $this->container->get('fos_elastica.index.theaterjobs.profile');
                    $newsIndex = $this->container->get('fos_elastica.index.theaterjobs.news');
                    $newsFinder = $this->container->get('fos_elastica.finder.theaterjobs.news');
                    $jobFinder = $this->container->get('fos_elastica.index.theaterjobs.job');

                    $queryNews = $fosElastica->getRepository(News::class)->relatedNews($organization->getId());
                    $relatedNews = $newsIndex->search($queryNews)->getTotalHits();
                    $queryProfile = $fosElastica->getRepository(Organization::class)->relatedPeople($organization->getId(), $this->isGranted('ROLE_ADMIN'));
                    $relatedPeople = $profileIndex->search($queryProfile)->getTotalHits();
                    $queryReljobs = $fosElastica->getRepository(Organization::class)->relatedJobs($organization->getId());
                    $related_jobs = $jobFinder->search($queryReljobs)->getAggregations();

                    $activity = $this->getESUserActivity(Organization::class, $organization->getId());
                    // Mark entity Seen
                    $this->viewEvent(Organization::class, $organization->getId(), $user);
                    $grants = $this->getOrderedGrants($organization);
                    $listPerformance = $this->getAll($organization);

                    $formName = OrganizationNameType::class;
                    $routeParams = ['slug' => $organization->getSlug()];
                    $nameForm = $this->createEditForm($formName, $organization, [], 'tj_main_organization_name_edit', $routeParams);

                    $formName = OrganizationLogoType::class;
                    $logoForm = $this->createEditForm($formName, $organization, [], 'tj_organization_update_logo', $routeParams);

                    $teamMemberPendingJobPublication = $this->em->getRepository(Job::class)->getRequestsTeamMembers($organization->getId());

                    $hasTeamMembershipApplication = $this->em->getRepository(TeamMembershipApplication::class)->checkIfHasUnapprovedApplications($user, $organization);

                    $newsApplicationQuery = $fosElastica->getRepository(News::class)->searchApplication([
                        'id' => $organization->getId(),
                        'tag' => 'application'
                    ]);

                    $application = $newsFinder->find($newsApplicationQuery);

                    $params = [
                        'entity' => $organization,
                        'formLogo' => $logoForm->createView(),
                        'listPerformances' => $listPerformance,
                        'nameForm' => $nameForm->createView(),
                        'activity' => $activity,
                        'application' => $application,
                        'related_news' => $relatedNews,
                        'related_people' => $relatedPeople,
                        'grantsList' => $grants,
                        'canEdit' => $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($user),
                        'related_jobs' => $related_jobs,
                        'orchestra' => $organization->isOrchestra(),
                        'teamMemberPendingJobPublicationRequests' => $teamMemberPendingJobPublication,
                        'hasTeamMembershipApplication' => $hasTeamMembershipApplication
                    ];

                    if ($this->isGranted('ROLE_ADMIN')) {
                        $adminCommentForm = $this->createCommentForm();
                        $adminCommentForm->get('organization')->setData($organization);
                        $formName = OrganizationStatusType::class;
                        $routeParams = ['slug' => $organization->getSlug()];
                        $statusForm = $this->createEditForm($formName, $organization, [], 'tj_organization_status', $routeParams);
                        $params['commentsForm'] = $adminCommentForm->createView();
                        $params['statusForm'] = $statusForm->createView();

                    }

                    $content = $this->render('TheaterjobsInserateBundle:Organization:showOrganization.html.twig', $params);

                    return $this->generalCustomCacheControlDirective([
                        'html' => $content->getContent(),
                        'url' => $this->generateUrl('tj_organization_show', ['slug' => $organization->getSlug()])
                    ]);

                }
            } else {
                $errors = $this->getErrorMessagesAJAX($editForm);
            }
            return new JsonResponse(['errors' => $errors]);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Displays a form to edit an existing Organization entity.
     *
     * @param Organization $organization
     * @return mixed
     * @Route("/edit/{slug}", name="tj_main_organization_edit", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Organization $organization)
    {
        if (!$this->isGranted('ROLE_ADMIN') && !($organization->isTeamMember($this->getUser()))) {
            throw new AccessDeniedException();
        }

        $orgaStage = $organization->getOrganizationStage();
        $tags_titles = [];

        foreach ($orgaStage as $stage) {
            $tags = '';
            foreach ($stage->getTags() as $tag) {
                $tags .= $tag->getTitle() . ',';
            }
            $tags_titles[$stage->getId()] = substr_replace($tags, "", -1);
        }

        $orgaEnsemble = $organization->getOrganizationEnsemble();
        $usersIds = [];

        foreach ($orgaEnsemble as $ensemble) {
            $users = '';
            foreach ($ensemble->getUsers() as $user) {
                $users .= $user->getId() . ',';
            }
            $usersIds[$ensemble->getId()] = substr_replace($users, "", -1);
        }

        $formName = OrganizationType::class;
        $routeParams = ['slug' => $organization->getSlug()];
        $form = $this->createEditForm($formName, $organization, [], 'tj_main_organization_update', $routeParams);

        return $this->render('TheaterjobsInserateBundle:Modal:organizationData.html.twig', [
            'entity' => $organization,
            'edit_form' => $form->createView(),
            'tag_titles' => $tags_titles,
            'users' => $usersIds]);
    }

    /**
     * Edits an existing Organization entity.
     *
     * @param Request $request Represents a HTTP request.
     * @param Organization $organization The organization object.
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Route("/update/{slug}", name="tj_main_organization_update")
     * @Method("PUT")
     * @Security("is_authenticated()")
     */
    public function updateAction(Request $request, Organization $organization)
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$organization->isTeamMember($this->getUser())) {
            throw new AccessDeniedException();
        }
        $oldEnsembles = new ArrayCollection();
        foreach ($organization->getOrganizationEnsemble() as $ensemble) {
            $oldEnsembles->add($ensemble);
        }
        $oldStaff = new ArrayCollection();
        foreach ($organization->getOrganizationStaff() as $staff) {
            $oldStaff->add($staff);
        }
        $formName = OrganizationType::class;
        $routeParams = ['slug' => $organization->getSlug()];
        $form = $this->createEditForm($formName, $organization, [], 'tj_main_organization_update', $routeParams);
        $form->handleRequest($request);
        if ($form->isValid()) {
            foreach ($oldStaff as $staff) {
                if (false === $organization->getOrganizationStaff()->contains($staff)) {
                    $organization->getOrganizationStaff()->removeElement($staff);
                }
                $this->getEM()->remove($staff);
            }
            foreach ($oldEnsembles as $ensemble) {
                if (false === $organization->getOrganizationEnsemble()->contains($ensemble)) {
                    $organization->getOrganizationEnsemble()->removeElement($ensemble);
                }
                $this->getEM()->remove($ensemble);
            }
            foreach ($organization->getOrganizationEnsemble() as $ensemble) {
                if ($ensemble->getUsers() !== null) {
                    foreach ($ensemble->getUsers() as $oldUser) {
                        $ensemble->removeUser($oldUser);
                    }
                }
            }
            if (isset($request->request->get('tj_inserate_form_organization')['organizationEnsemble'])) {
                $entity = $form['organizationEnsemble']->getData();
                foreach ($request->request->get('tj_inserate_form_organization')['organizationEnsemble'] as $ensId => $ensStage) {
                    $numArray = explode(",", $ensStage['users_helper']);
                    foreach ($numArray as $num) {
                        if (strlen($num) > 0) {
                            $entity[$ensId]->addUser($this->handleUsers($num));
                        }
                    }
                    $this->getEM()->persist($entity[$ensId]);
                }
            }
            $organization->setUpdatedAt(new \DateTime());
            $this->getEM()->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedData', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationData.html.twig', array(
                    'entity' => $organization,
                    'canEdit' => $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser())
                )
            );
            // Handle XHR here
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }
        return new JsonResponse([
            'errors' => $this->getErrorMessages($form)
        ]);
    }

    /**
     * Deletes a AdminComments entity.
     *
     * @Route("/delete/{slug}", name="tj_organization_delete")
     * @Method("GET")
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Organization $organization)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $em = $this->getEM();
            $users = $this->getRepository('TheaterjobsInserateBundle:Organization')->findActiveUsers($organization->getId());

            $jobs = $this->getRepository('TheaterjobsInserateBundle:Organization')->findPublishedJobsByOrganization($organization, $em);
            if (count($jobs) > 0) {
                $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')->trans("flash.error.organization.has_active_jobs %name%", array(
                        '%name%' => $organization->getName()), 'flashes'));
            } elseif (count($users) > 0) {
                $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')->trans("flash.error.organization.has_active_users %name%", array(
                        '%name%' => $organization->getName()), 'flashes'));
            } else {
                $organization->setArchivedAt(new \DateTime());
                $organization->setDestroyedAt(null);
                $organization->setNotReachableAt(null);
                $em->persist($organization);
                $em->flush();
                $this->get('session')->getFlashBag()
                    ->add('success', $this->get('translator')->trans("flash.success.organization_archived %name%", array(
                        '%name%' => $organization->getName()), 'flashes'));
                $dispatcher = $this->get('event_dispatcher');
                $uacEvent = new \Theaterjobs\UserBundle\Event\UserActivityEvent($organization, $this->getTranslator()->trans('tj.user.activity.archived.organization', array(), 'activity'));
                $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            }
        } else {
            $this->get('session')->getFlashBag()
                ->add('danger', $this->get('translator')->trans("flash.error.organization.no_permissions %name%", array(
                    '%name%' => $organization->getName()), 'flashes'));
        }
        return $this->redirect($this->generateUrl('tj_main_organization_home'));
    }

    /**
     * @Route("/update/logo/{slug}", name="tj_organization_update_logo", options={"expose"=true})
     * @Method({"PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateImageAction(Request $request, Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $formName = OrganizationLogoType::class;
        $routeParams = ['slug' => $organization->getSlug()];
        $editForm = $this->createEditForm($formName, $organization, [], 'tj_organization_update_logo', $routeParams);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Find changes
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedOrganizationLogo', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            if ($request->isXmlHttpRequest()) {
                // Handle XHR here
                return $this->render('TheaterjobsInserateBundle:Partial:headerLogo.html.twig', array(
                        'entity' => $organization,
                        'formLogo' => $editForm->createView(),
                        'canEdit' => $organization->isTeamMember($this->getUser())
                    )
                );
            }

            return $this->redirectToRoute('tj_organization_show', ['slug' => $organization->getSlug()]);
        }
    }

    /**
     *
     * @Route("/edit/description/{slug}", name="tj_organization_description", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @Route("/edit/description/{modal}/{slug}", name="tj_organization_modal_description", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param null $modal
     * @param Organization $organization
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     * @TODO Refactor this 'action'
     */
    public function descriptionAction(Request $request, $modal = null, Organization $organization)
    {
        $editForm = $this->createEditForm('tj_inserate_form_organization_descr', $organization, [], 'tj_organization_description', ['slug' => $organization->getSlug()]);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $organization->setUpdatedAt(new \DateTime());

            $dispatcher = $this->get('event_dispatcher');

            //In order to detect changes of field content we store the old description.
            $oldDescription = $organization->getDescription();

            //If new inserted description is different from old one , we store the data as an OBJECT into a SERIALIZED ARRAY with field name, old and new value.
            //It is stored this way because where it is displayed , the array is unserialized and than looped.
            if ($editForm->get('description')->getData() != $oldDescription) {
                $changedFields = serialize([
                    (object)array(
                        'field' => $this->get('translator')->trans('inserate.organization.logDetails.field.description', array()),
                        'old' => $oldDescription,

                        'new' => $editForm->get('description')->getData()
                    )]);
            } else {
                $changedFields = null;
            }

            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedOrganizationDescription', array(), 'activity'), false, $changedFields);


            $uacEvent->setFlush(false);
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $this->em->flush();

            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationDescription.html.twig',
                [
                    'entity' => $organization,
                    'canEdit' => $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser())
                ]
            );

            // Handle XHR here
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }

        return $this->render('TheaterjobsInserateBundle:Modal:desc.html.twig', ['entity' => $organization,
                'edit_form' => $editForm->createView()
            ]
        );

    }


    /**
     *
     * @Route("/edit/application-info/{slug}", name="tj_organization_application_info", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @Route("/edit/application-info/{modal}/{slug}", name="tj_organization_modal_application_info", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function applicationInfoAction(Request $request, Organization $organization)
    {
        $editForm = $this->createEditForm('tj_inserate_form_organization_application_info', $organization, [], 'tj_organization_application_info', ['slug' => $organization->getSlug()]);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Find changes
            $organization->setUpdatedAt(new \DateTime());
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedOrganizationApplicationInfo', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            // Handle XHR here
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationApplicationInfo.html.twig',
                [
                    'entity' => $organization,
                    'canEdit' => $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser())
                ]
            );
            // Handle XHR here
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }

        return $this->render('TheaterjobsInserateBundle:Modal:editApplicationInfo.html.twig',
            ['entity' => $organization,
                'edit_form' => $editForm->createView()
            ]
        );

    }


    /**
     *
     * @Route("/edit/location/{slug}", name="tj_organization_location", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @Route("/edit/location/{modal}/{slug}", name="tj_organization_modal_location", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function locationAction(Request $request, Organization $organization)
    {
        $type = OrganizationLocationType::class;
        $routeParms = ['slug' => $organization->getSlug()];
        $routeName = "tj_organization_location";
        $editForm = $this->createEditForm($type, $organization, [], $routeName, $routeParms);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Find changes
            $organization->setUpdatedAt(new \DateTime());
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updated.organizationLocation', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationLocation.html.twig',
                [
                    'entity' => $organization,
                    'canEdit' => $organization->isTeamMember($this->getUser())
                ]
            );
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }
        return $this->render('TheaterjobsInserateBundle:Modal:map.html.twig', [
            'entity' => $organization,
            'edit_form' => $editForm->createView()
        ]);
    }


    /**
     * Open modal that shows organization location on map
     *
     * @Route("/show/location/{slug}", name="tj_organization_show_location", defaults={"slug" = null})
     * @param Organization $organization
     * @return Response
     */
    public function showLocationOrganization(Organization $organization)
    {

        return $this->render('TheaterjobsInserateBundle:Modal:mapShow.html.twig', [
            'entity' => $organization,
        ]);
    }

    /**
     *
     * @Route("/edit/stages/{slug}", name="tj_main_organization_stages_edit", defaults={"slug" = null})
     * @Route("/edit/stages/{modal}/{slug}", name="tj_main_organization_stages_modal_edit", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param null $modal
     * @param Organization $organization
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editStagesAction(Request $request, $modal = null, Organization $organization)
    {
        $canEdit = ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser()));
        if (!$canEdit) {
            throw $this->createAccessDeniedException('You are not authorized to perform this action.');
        }
        $type = OrganizationStagesType::class;
        $routeParams = ['slug' => $organization->getSlug()];
        $editForm = $this->createEditForm($type, $organization, [], 'tj_main_organization_stages_edit', $routeParams);

        $oldStages = new ArrayCollection();
        foreach ($organization->getOrganizationStage() as $stage) {
            $oldStages->add($stage);
        }

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Find changes
            foreach ($organization->getOrganizationStage() as $stage) {
                if ($stage->getTags() !== null) {
                    foreach ($stage->getTags() as $oldTag) {
                        $stage->removeTag($oldTag);
                    }
                }
            }

            if (isset($request->request->get('tj_inserate_form_organization_stages')['organizationStage'])) {
                $entity = $editForm['organizationStage']->getData();
                foreach ($request->request->get('tj_inserate_form_organization_stages')['organizationStage'] as $orgaId => $orgaStage) {
                    $numArray = explode(",", $orgaStage['tags_helper']);
                    foreach ($numArray as $num) {
                        if (strlen($num) > 0) {
                            $entity[$orgaId]->addTag($this->handleTags($num));
                        }
                    }
                    $this->getEM()->persist($entity[$orgaId]);
                }
            }

            foreach ($oldStages as $stage) {
                if (false === $organization->getOrganizationStage()->contains($stage)) {
                    $organization->getOrganizationStage()->removeElement($stage);
                }
                $this->getEM()->remove($stage);
            }

            $organization->setUpdatedAt(new \DateTime());
            $this->em->persist($organization);
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedOrganizationStages', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            if ($request->isXmlHttpRequest()) {
                // Handle XHR here
                $activity = $this->getESUserActivity(Organization::class, $organization->getId());
                $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
                $content = $this->render('TheaterjobsInserateBundle:Partial:organizationStages.html.twig',
                    [
                        'entity' => $organization,
                        'canEdit' => $canEdit
                    ]
                );
                // Handle XHR here
                return new JsonResponse ([
                    'logs' => $logs->getContent(),
                    'content' => $content->getContent()
                ]);

            }

            return $this->redirectToRoute('tj_organization_show', ['slug' => $organization->getSlug()]);
        }

        if ($request->isXmlHttpRequest() && $editForm->isSubmitted() && !$editForm->isValid()) {
            return new JsonResponse(
                [
                    'errors' => $this->getErrorMessages($editForm)
                ]
            );
        }

        $template = 'TheaterjobsInserateBundle:Modal:organizationStages.html.twig';
        if ($modal) {
            $template = 'TheaterjobsInserateBundle:Modal:organizationStages.html.twig';
        }

        $orgaStage = $organization->getOrganizationStage();
        $tags_titles = array();

        foreach ($orgaStage as $stage) {
            $tags = '';
            foreach ($stage->getTags() as $tag) {
                $tags .= $tag->getTitle() . ',';
            }
            $tags_titles[$stage->getId()] = substr_replace($tags, "", -1);
        }

        return $this->render($template, ['entity' => $organization,
                'edit_form' => $editForm->createView(),
                'tag_titles' => $tags_titles
            ]
        );

    }

    /**
     *
     * @Route("/edit/performances/{slug}", name="tj_main_organization_performances_edit", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @Route("/edit/performances/{modal}/{slug}", name="tj_main_organization_performances_modal_edit", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editPerformancesAction(Request $request, Organization $organization)
    {
        $canEdit = ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser()));
        if (!$canEdit) {
            throw $this->createAccessDeniedException('You are not authorized to perform this action.');
        }
        $editForm = $this->createEditForm('tj_inserate_form_organization_performances', $organization, [], 'tj_main_organization_performances_edit', ['slug' => $organization->getSlug()]);
        $oldVisitors = new ArrayCollection();
        foreach ($organization->getOrganizationVisitors() as $visitors) {
            $oldVisitors->add($visitors);
        }

        $oldGrants = new ArrayCollection();
        foreach ($organization->getOrganizationGrants() as $grant) {
            $oldGrants->add($grant);
        }

        $oldPerformance = new ArrayCollection();
        foreach ($organization->getOrganizationPerformance() as $performance) {
            $oldPerformance->add($performance);
        }
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            foreach ($oldVisitors as $visitor) {
                if (false === $organization->getOrganizationVisitors()->contains($visitor)) {
                    $organization->getOrganizationVisitors()->removeElement($visitor);
                }
                $this->getEM()->remove($visitor);
            }

            foreach ($oldPerformance as $performance) {
                if (false === $organization->getOrganizationPerformance()->contains($performance)) {
                    $organization->getOrganizationPerformance()->removeElement($performance);
                }
                $this->getEM()->remove($performance);
            }

            $organization->setUpdatedAt(new \DateTime());
            // Find changes
            $this->em->persist($organization);
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedPerformanceVisitors', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $listPerformance = $this->getAll($organization);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationPerformances.html.twig',
                [
                    'entity' => $organization,
                    'listPerformances' => $listPerformance,
                    'canEdit' => $canEdit
                ]
            );
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }
        if ($request->isMethod('PUT') && !$editForm->isValid()) {
            return new JsonResponse([
                'errors' => $this->getErrorMessages($editForm)
            ]);
        }
        return $this->render('TheaterjobsInserateBundle:Modal:organizationPerformances.html.twig', [
            'entity' => $organization,
            'edit_form' => $editForm->createView()
        ]);
    }

    /**
     *
     * @Route("/edit/grants/{slug}", name="tj_main_organization_grants_edit", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @Route("/edit/grants/{modal}/{slug}", name="tj_main_organization_grants_modal_edit", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editGrantsAction(Request $request, Organization $organization)
    {
        $canEdit = ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser()));
        if (!$canEdit) {
            throw $this->createAccessDeniedException('You are not authorized to perform this action.');
        }
        $oldGrants = new ArrayCollection();
        foreach ($organization->getOrganizationGrants() as $grant) {
            $oldGrants->add($grant);
        }

        $editForm = $this->createEditForm('tj_inserate_form_organization_budgets', $organization, [], 'tj_main_organization_grants_edit', ['slug' => $organization->getSlug()]);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Find changes
            foreach ($oldGrants as $grant) {
                if (false === $organization->getOrganizationGrants()->contains($grant)) {
                    $organization->getOrganizationGrants()->removeElement($grant);
                }
                $this->getEM()->remove($grant);
            }
            $organization->setUpdatedAt(new \DateTime());
            $this->em->persist($organization);
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedBudgetGrants', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $grants = $this->getOrderedGrants($organization);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationGrants.html.twig',
                [
                    'entity' => $organization,
                    'grantsList' => $grants,
                    'canEdit' => $canEdit
                ]
            );
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }
        if ($request->isMethod('PUT') && !$editForm->isValid()) {
            return new JsonResponse([
                'errors' => $this->getErrorMessages($editForm)
            ]);
        }
        return $this->render('TheaterjobsInserateBundle:Modal:organizationGrants.html.twig', [
            'entity' => $organization,
            'edit_form' => $editForm->createView()
        ]);
    }

    /**
     *
     * @Route("/edit/contact/{slug}", name="tj_organization_contact", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @Route("/edit/contact/{modal}/{slug}", name="tj_organization_modal_contact", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editContactAction(Request $request, Organization $organization)
    {
        $oldContact = ($organization->getContactSection()) ? $organization->getContactSection()->getContact() : '';
        $oldEmail = ($organization->getContactSection()) ? $organization->getContactSection()->getEmail() : '';

        if (!$organization->getContactSection()) {
            $contactSection = new ContactSection();
            $contactSection->setOrganization($organization);
            $organization->setContactSection($contactSection);
            $this->em->persist($organization);
        }

        $editForm = $this->createEditForm('tj_inserate_form_organization_contact', $organization, [], 'tj_organization_contact', ['slug' => $organization->getSlug()]);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $subData = [];
            $changedValues = [];
            foreach ($editForm->get('contactSection') as $subForm) {
                $subData[] = ($subForm->getData() != '') ? $subForm->getData() : '';
            }

            if ($subData[0] != $oldContact) {
                array_push($changedValues,
                    (object)array('field' => $this->get('translator')->trans('inserate.organization.logDetails.field.contact', array()), 'old' => $oldContact, 'new' => $subData[0])
                );
            }

            if ($subData[1] != $oldEmail) {
                array_push($changedValues,
                    (object)array('field' => $this->get('translator')->trans('inserate.organization.logDetails.field.email', array()), 'old' => $oldEmail, 'new' => $subData[1])
                );
            }

            $organization->setUpdatedAt(new \DateTime());
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $changedFields = (count($changedValues) > 0) ? serialize($changedValues) : null;

            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.updatedContact', array(), 'activity'), false, $changedFields);
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationContact.html.twig',
                [
                    'entity' => $organization,
                    'canEdit' => $organization->isTeamMember($this->getUser())
                ]
            );
            return new JsonResponse ([
                'logs' => $logs->getContent(),
                'content' => $content->getContent()
            ]);
        }

        if ($request->isXmlHttpRequest() && $editForm->isSubmitted() && !$editForm->isValid()) {
            return new JsonResponse([
                'errors' => $this->getErrorMessages($editForm)
            ]);
        }

        return $this->render('TheaterjobsInserateBundle:Modal:contact.html.twig', ['entity' => $organization,
            'edit_form' => $editForm->createView(),
            'socialSize' => count($this->em->getRepository('TheaterjobsAdminBundle:SocialMedia')->findAll())
        ]);

    }

    /**
     *
     * @Route("/all/stages/{slug}", name="tj_main_organization_stages_all", defaults={"slug" = null})
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response*
     * @internal param $modal
     */
    public function showStagesAction(Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $canEdit = $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser());

        return $this->render('TheaterjobsInserateBundle:Modal:stagesAll.html.twig', [
            'entity' => $organization,
            'canEdit' => $canEdit
        ]);

    }

    /**
     *
     * @Route("/all/budgets/{slug}", name="tj_main_organization_budgets_all", defaults={"slug" = null})
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @internal param $modal
     */
    public function showBudgetAllAction(Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $grants = $this->getOrderedGrants($organization);
        $template = 'TheaterjobsInserateBundle:Modal:budgetsAll.html.twig';
        $canEdit = ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser()));

        return $this->render($template, [
                'entity' => $organization,
                'grantsList' => $grants,
                'canEdit' => $canEdit
            ]
        );

    }

    /**
     *
     * @Route("/all/visitors/{slug}", name="tj_main_organization_visitors_all", defaults={"slug" = null})
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @internal param $modal
     */
    public function showVisitorsAllAction(Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }
        $listPerformance = $this->getAll($organization);
        $template = 'TheaterjobsInserateBundle:Modal:visitorsAll.html.twig';
        $canEdit = ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser()));

        return $this->render($template, [
                'entity' => $organization,
                'listPerformances' => $listPerformance,
                'canEdit' => $canEdit
            ]
        );

    }

    /**
     *
     * @Route("/all/logs/{slug}", name="tj_organization_all_logs", defaults={"slug" = null}, condition="request.isXmlHttpRequest()")
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function showLogsAction(Organization $organization)
    {

        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $activity = $this->getRepository('TheaterjobsInserateBundle:Organization')->findOrgaActivity($organization, $this->getEM()->createQueryBuilder());

        $template = 'TheaterjobsInserateBundle:Modal:logsAll.html.twig';

        return $this->render($template, [
                'entity' => $organization,
                'activity' => $activity
            ]
        );

    }

    /**
     *
     * @Route("/all/members/{slug}", name="tj_organization_all_members", defaults={"slug" = null})
     * @ParamConverter("organization", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function showMembersAction(Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        return $this->render('TheaterjobsInserateBundle:Modal:membersAll.html.twig', [
                'entity' => $organization,
                'canEdit' => $organization->isTeamMember($this->getUser())
            ]
        );

    }


    /**
     * Merri ket mos le ansja
     * @param Organization $organization
     * @return array
     */
    public function getAll(Organization $organization)
    {
        $list = [];

        foreach ($organization->getOrganizationPerformance() as $performance) {
            $list[$performance->getSeason()]['performance'] = $performance->getPerformanceNumber();
            if (!isset($list[$performance->getSeason()]['visitors'])) {
                $list[$performance->getSeason()]['visitors'] = '';
            }
        }

        foreach ($organization->getOrganizationVisitors() as $visitor) {
            $list[$visitor->getSeason()]['visitors'] = $visitor->getVisitorsNumber();
            if (!isset($list[$visitor->getSeason()]['performance'])) {
                $list[$visitor->getSeason()]['performance'] = '';
            }
        }

        if ($list) {
            krsort($list);
        }
        return $list;
    }

    /**
     *
     * @Route("/visible/{slug}/{publish}", name="tj_organization_visible", options={"expose"=true})
     * @param Organization $organization
     * @param $publish
     * @return string|JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function organizationVisibleAction(Organization $organization, $publish)
    {
        $response = '';
        $continue = true;

        // Save old visibility
        $oldVisibility = $organization->getIsVisibleInList();

        if ($publish == 0) {
            $organization->setIsVisibleInList(false);

            // Check if organization is becoming invisible
            if ($oldVisibility == true && ($oldVisibility != $organization->getIsVisibleInList())) {
                // Check if the form changes can be persisted in database
                $continue = $this->checkOrganizationAvailability($organization);
            }
            // Return error when organization has jobs published or members
            if (!$continue) {
                return new JsonResponse([
                    'error' => true,
                    "text" => $this->get('translator')->trans('organization.hasPublishedJobs.orMembers', array(), 'forms')
                ]);
            }

            $this->em->persist($organization);
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.admin.changedOrganizationIntoInvisible', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            $text = '<h5>The organization is invisible now.</h5>';

            $response = new JsonResponse([
                'logs' => $logs->getContent(),
                'unpublish' => true,
                'text' => $text
            ]);
        } elseif ($publish == 1) {
            $organization->setIsVisibleInList(true);
            $this->em->persist($organization);
            $this->em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->get('translator')->trans('organization.activity.label.admin.changedOrganizationIntoVisible', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);

            $text = '<h5>The organization is visible now.</h5>';
            $response = new JsonResponse([
                'logs' => $logs->getContent(),
                'publish' => true,
                'text' => $text
            ]);
        }

        return $response;

    }

    /**
     *
     * @Route("/status/{slug}", name="tj_organization_status", options={"expose"=true})
     * @param Request $request
     * @param Organization $organization
     * @return string|JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function organizationStatusAction(Request $request, Organization $organization)
    {
        if (!$organization) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }

        $oldStatus = $organization->getStatus();
        $continue = true;
        // @todo translation needed
        $status = [
            Organization::PENDING => 'Pending',
            Organization::ACTIVE => 'Active',
            Organization::UNKNOWN => 'Unknown',
            Organization::CLOSED => 'Closed'
        ];

        $editForm = $this->createEditForm(OrganizationStatusType::class,
            $organization,
            [],
            'tj_organization_status',
            ['slug' => $organization->getSlug()]
        );

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            // Check if organization is changing from active status
            if ($oldStatus == Organization::ACTIVE && ($oldStatus != $organization->getStatus())) {
                // Check if the form changes can be persisted in database
                $continue = $this->checkOrganizationAvailability($organization);
            }

            // Return error when organization has jobs published or members
            if (!$continue) {
                return new JsonResponse([
                    'error' => true,
                    'message' => $this->getTranslator()->trans('organization.hasPublishedJobs.orMembers', [], 'forms')
                ]);
            }
            // Persist change on DB
            $organization->setUpdatedAt(Carbon::now());

            if ($organization->getStatus() != Organization::ACTIVE) {
                $organization->setStatusChangedAt(Carbon::now());
            }

            $this->em->persist($organization);
            $this->em->flush();


            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($organization, $this->getTranslator()->trans('organization.activity.label.admin.changedOrganizationStatusInto %str% on', [
                '%str%' => $status[$organization->getStatus()]
            ], 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $activity = $this->getESUserActivity(Organization::class, $organization->getId());
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);

            if ($request->isXmlHttpRequest()) {
                // Handle XHR here
                return new JsonResponse([
                    'logs' => $logs->getContent(),
                    'status' => true,
                    'text' => $this->getTranslator()->trans('organization.status.Change') . $status[$organization->getStatus()]
                ]);
            }

            return $this->redirectToRoute('tj_organization_show', ['slug' => $organization->getSlug()]);
        }
        return $this->redirectToRoute('tj_organization_show', ['slug' => $organization->getSlug()]);
    }

    /**
     *
     * @Route("/get-org-by-name/{name}", name="tj_organization_data", options={"expose"=true})
     * @param $name
     * @return JsonResponse
     */
    public function getOrganizationLogoByName($name)
    {
        if (!$name) {
            throw $this->createNotFoundException('Unable to find Organization entity.');
        }
        $org = $this->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(['name' => $name]);

        if ($org->getPath()) {
            $url = $this->get('templating.helper.assets')->getUrl('uploads/organization/logo/' . $org->getPath());
        } else {
            $url = $this->get('templating.helper.assets')->getUrl('bundles/theaterjobsmain/images/profile-placeholder.svg');
        }

        if ($org) {
            return new JsonResponse([
                'url' => $url,
                'latLng' => $org->getGeolocation(),
                'contact' => $org->getContactSection() ? $org->getContactSection()->getContact() : ""
            ]);
        }
    }


    /**
     * Creates a new membership application for an Organization entity.
     * @param Request $request Represents a HTTP request.
     * @param Organization $organization
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route("/apply-for-team-membership/{slug}", name="tj_main_organization_create_team_membership_application" , condition="request.isXmlHttpRequest()")
     * @Method({"GET", "POST"})
     */
    public function applyForTeamMembership(Request $request, Organization $organization)
    {

        if (!$this->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('fos_user_security_login'));

        if (!$organization)
            throw $this->createNotFoundException('Unable to find Organization entity.');

        $ent = $this->getEM()->getRepository('TheaterjobsUserBundle:UserOrganization')
            ->findOneBy(array('user' => $this->getUser(), 'organization' => $organization));

        if ($ent) {
            if (!$ent->getRevokedAt()) {
                return new JsonResponse([
                    'error' => true,
                    "message" => $this->getTranslator()->trans('user.organization.alreadymember', array(), 'forms')
                ]);
            }
        }

        $entity = new TeamMembershipApplication();
        $formName = TeamMembershipApplicationType::class;
        $routeName = "tj_main_organization_create_team_membership_application";
        $routeParams = ['slug' => $organization->getSlug()];
        $form = $this->createCreateForm($formName, $entity, [], $routeName, $routeParams);

        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $pendingApplication = $em->getRepository("TheaterjobsInserateBundle:TeamMembershipApplication")->checkIfHasUnapprovedApplications($user, $organization);

        if ($form->isValid()) {
            if (!$organization->isActive()) {
                return new JsonResponse([
                    'error' => true,
                    "message" => $this->get('translator')->trans('organization.teamMembership.notActive', array(), 'forms')
                ]);
            }
            if (!$pendingApplication) {
                $entity->setOrganization($organization);

                $emailContent = $form["applicationText"]->getData();

                $entity->setApplicationText($emailContent);
                $entity->setUser($user);
                $entity->setCreatedAt(Carbon::now());
                $em->persist($entity);
                $em->flush();
                $notification = new Notification();

                $title = 'organization.team.member.application.please.wait %organization%';
                $transParams = ['%organization%' => $organization->getName()];
                $linkParams = ['slug' => $organization->getSlug()];

                $notification
                    ->setTitle($title)
                    ->setTranslationKeys($transParams)
                    ->setCreatedAt(Carbon::now())
                    ->setDescription('')
                    ->setLink('tj_organization_show')
                    ->setLinkKeys($linkParams);

                $event = (new NotificationEvent())
                    ->setObjectClass(TeamMembershipApplication::class)
                    ->setObjectId($entity->getId())
                    ->setNotification($notification)
                    ->setUsers($this->getUser())
                    ->setType('team_membership_application');

                $this->get('event_dispatcher')->dispatch('notification', $event);

                return new JsonResponse([
                    'error' => false,
                    'message' => $this->getTranslator()->trans('organization.success.teamMembershipApplication', array(), 'messages'),
                ]);
            } else {
                return new JsonResponse(['error' => true, 'message' => 'You have a pending team membership application towards this organization. Pleas wait patiently while 
            our admins review your existing application.']);
            }
        }


        if ($request->isXmlHttpRequest() && $form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(['errors' => $this->getErrorMessages($form)]);
        }

        $template = 'TheaterjobsInserateBundle:Modal:newTeamMembershipApplication.html.twig';
        return $this->render($template, [
                'entity' => $organization,
                'form' => $form->createView(),
            ]
        );

    }

    /**
     * @Route("/get/tags", name="tj_organization_get_tags", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function tagTagsSuggestAction(Request $request)
    {
        $em = $this->getEM();
        $repo = $em->getRepository(Tags::class);
        $tags = $repo->tagSuggest($request->query->get('q'));
        $newCheck = $request->query->getBoolean('newCheck', false);

        $pagination = $this->paginator->paginate(
            $tags, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        $response = [];


        foreach ($pagination as $t) {
            $tag['id'] = $t->getTitle();
            $tag['text'] = $t->getTitle();
            $tag['disabled'] = $newCheck;
            $tag['total_count'] = $pagination->getTotalItemCount();
            $response[] = $tag;
        }

        return new JsonResponse($response);
    }

    /**
     * @param Organization $organization
     * @return bool
     */
    public function checkOrganizationAvailability(Organization $organization)
    {

        $fosElastica = $this->container->get('fos_elastica.manager');
        $jobIndex = $this->container->get('fos_elastica.index.theaterjobs.job');
        // Count published job/education offers
        $queryCountJobs = $fosElastica->getRepository(Organization::class)->publishedRelatedJobs($organization->getId());
        $publishedJobs = $jobIndex->search($queryCountJobs)->getTotalHits();

        // Count all users
        $activeMembers = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')
            ->countActiveMembers($organization->getId());

        // Organization status cannot be changes because of team members or jobs publicated
        return !($activeMembers > 0 || $publishedJobs > 0);
    }
}
