<?php

namespace Theaterjobs\MainBundle\Controller;

use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;
use Theaterjobs\AdminBundle\Form\AdminBillingType;
use Theaterjobs\AdminBundle\Form\AdminPeopleType;
use Theaterjobs\AdminBundle\Model\AdminBillingSearch;
use Theaterjobs\AdminBundle\Model\AdminPeopleSearch;
use Theaterjobs\InserateBundle\Entity\ApplicationTrack;
use Theaterjobs\InserateBundle\Entity\Job;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Theaterjobs\MainBundle\Entity\SaveSearch;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Entity\Skill;
use Theaterjobs\ProfileBundle\Form\Type\ActualityType;
use Theaterjobs\UserBundle\Entity\Notification;

/**
 * The Default Controller.
 *
 * It provides the index action.
 *
 * @category Controller
 * @package  Theaterjobs\MainBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /**
     * @DI\Inject("sonata.seo.page")
     * @var SeoPage
     */
    private $seo;

    /**
     * @DI\Inject("knp_paginator")
     * @var Paginator
     */
    private $paginator;

    /**
     * @DI\Inject("translator")
     * @var Translator $trans
     */
    private $trans;

    /**
     * The index action.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response $array
     * @Route("/", options={"i18n" = false})
     * @Route("/home", name="tj_main_default_home", options={"i18n" = false})
     */
    public function indexAction()
    {
        $title = $this->trans->trans("default.homepage.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.homepage.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.homepage.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);


        if ($this->isGranted('ROLE_USER')) {
            // Redirect to the dashboard
            return $this->redirect($this->generateUrl('tj_main_dashboard_index'), 301);
        }
        $news = $this->getLatestNews(3);
        $randomProfileImages = $this->getRamdomImages(18);
        $job = $this->getLatestJobs(8);

        return $this->render('TheaterjobsMainBundle:Default:index.html.twig',
            [
                'randomProfiles' => $randomProfileImages,
                'news' => $news,
                'jobs' => $job
            ]
        );
    }

    /**
     * Callable from twig template (Base)
     * @return Response
     */
    public function adminSearchAction()
    {
        $peopleSearch = new AdminPeopleSearch();
        $adminPeopleSearchForm = $this->createGeneralSearchForm(AdminPeopleType::class,
            $peopleSearch,
            [],
            'admin_people_load'
        );

        $billingSearch = new AdminBillingSearch();
        $adminBillingSearchForm = $this->createGeneralSearchForm(AdminBillingType::class,
            $billingSearch,
            [],
            'admin_invoices_load'
        );

        return $this->render('TheaterjobsMainBundle:Partial/Navigation:adminSearch.html.twig', [
            'peopleAdmin' => $adminPeopleSearchForm->createView(),
            'billingAdmin' => $adminBillingSearchForm->createView()
        ]);
    }

    /**
     *
     * Landing page of jobs based on category
     *
     * @param Request $request
     * @param $category
     * @return Response $response
     *
     * @internal param Request $request
     * the .html extension is added for better google index
     * @Route("/jobs/{category}/job.html", name="tj_main_default_jobs_public", options={"i18n" = false})
     */
    public function jobPublic(Request $request, $category)
    {
        $options = $this->getSubcategoriesOptions($category);

        $this->seo->setTitle($options['title']);
        $this->seo->addMeta('name', 'description', $options['description']);
        $this->seo->addMeta('name', 'keywords', $options['keywords']);

        $result = $this->container->get('fos_elastica.index.theaterjobs.job');
        $jobElasticaRepo = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job');
        $queryPublished = $jobElasticaRepo->getPublicJobsForSubCategories($options['subCategoryIds'], 1);
        $queryArchived = $jobElasticaRepo->getPublicJobsForSubCategories($options['subCategoryIds'], 3);
        // Option 3b. KnpPaginator resultset
        /*page number*/
        $page = $request->query->getInt('page', 1);
        $paginationPublished = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $queryPublished, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );
        // get archived job in last 90 days
        $archivedJobs = $result->search($queryArchived, 1000);
        return $this->render('TheaterjobsMainBundle:Landingpage/index.html.twig', [
            'publishedJobs' => $paginationPublished,
            'archivedJobs' => $archivedJobs,
            'category' => $category,
            'options' => $options,
        ]);
    }

    /**
     * @param $category
     * @return array
     */
    private function getSubcategoriesOptions($category)
    {
        switch ($category) {
            case 'schauspieler':
                $options = [
                    'subCategoryIds' => [4, 5],
                    'categoryName' => 'Schauspiel',
                    'jobTypeMale' => 'Schauspieler',
                    'jobTypeFemale' => 'Schauspielerin',
                    'jobType' => 'Schauspiel',
                    'title' => $this->trans->trans("default.landingpage.title.schauspiel", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.schauspiel", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.schauspiel", array(), 'messages')
                ];
                break;
            case 'taenzer':
                $options = [
                    'subCategoryIds' => [11, 12],
                    'categoryName' => 'Tanz',
                    'jobTypeMale' => 'Tänzer',
                    'jobTypeFemale' => 'Tänzerin',
                    'jobType' => 'Tanz',
                    'title' => $this->trans->trans("default.landingpage.title.tanz", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.tanz", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.tanz", array(), 'messages')
                ];
                break;
            case 'saenger':
                $options = [
                    'subCategoryIds' => [19, 20],
                    'categoryName' => 'Gesang',
                    'jobTypeMale' => 'Sänger',
                    'jobTypeFemale' => 'Sängerin',
                    'jobType' => 'Sänger',
                    'title' => $this->trans->trans("default.landingpage.title.gesang", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.gesang", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.gesang", array(), 'messages')
                ];
                break;
            case 'theaterpaedagogik':
                $options = [
                    'subCategoryIds' => [68],
                    'categoryName' => 'Theaterpädagogik',
                    'jobTypeMale' => 'Theaterpädagoge',
                    'jobTypeFemale' => 'Theaterpädagogin',
                    'jobType' => 'Theaterpädagogik',
                    'title' => $this->trans->trans("default.landingpage.title.theaterpaedagogik", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.theaterpaedagogik", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.theaterpaedagogik", array(), 'messages')
                ];
                break;
            case 'buehnenbildner':
                $options = [
                    'subCategoryIds' => [38],
                    'categoryName' => 'Bühnenbild',
                    'jobTypeMale' => 'Bühnenbildner',
                    'jobTypeFemale' => 'Bühnenbildnerin',
                    'jobType' => 'Bühnenbild',
                    'title' => $this->trans->trans("default.landingpage.title.buehnenbild", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.buehnenbild", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.buehnenbild", array(), 'messages')
                ];
                break;
            case 'buehnenjobs':
                $options = [
                    'subCategoryIds' => [4, 5, 6, 10, 11, 12, 18, 19, 20],
                    'categoryName' => 'Bühnenjobs',
                    'jobTypeMale' => 'Theaterkünstler',
                    'jobTypeFemale' => 'Theaterkünstlerin',
                    'jobType' => 'Bühnenjobs',
                    'title' => $this->trans->trans("default.landingpage.title.buehnenjobs", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.buehnenjobs", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.buehnenjobs", array(), 'messages')
                ];
                break;
            case 'chorleiter':
                $options = [
                    'subCategoryIds' => [23],
                    'categoryName' => 'Chorleitung',
                    'jobTypeMale' => 'Chorleiter',
                    'jobTypeFemale' => 'Chorleiterin',
                    'jobType' => 'Chorleitungs',
                    'title' => $this->trans->trans("default.landingpage.title.chorleitung", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.chorleitung", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.chorleitung", array(), 'messages')
                ];
                break;
            case 'chorsaenger':
                $options = [
                    'subCategoryIds' => [24, 25, 26, 27, 28, 29, 30],
                    'categoryName' => 'Chor',
                    'jobTypeMale' => 'Chorsänger',
                    'jobTypeFemale' => 'Chorsängerin',
                    'jobType' => 'Chor',
                    'title' => $this->trans->trans("default.landingpage.title.chor", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.chor", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.chor", array(), 'messages')
                ];
                break;
            case 'dirigent':
                $options = [
                    'subCategoryIds' => [16],
                    'categoryName' => 'Musikalische Leitung',
                    'jobTypeMale' => 'Dirigent',
                    'jobTypeFemale' => 'Dirigentin',
                    'jobType' => 'Musikalische Leitungs',
                    'title' => $this->trans->trans("default.landingpage.title.musikalischeLeitung", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.musikalischeLeitung", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.musikalischeLeitung", array(), 'messages')
                ];
                break;
            case 'dramaturg':
                $options = [
                    'subCategoryIds' => [64],
                    'categoryName' => 'Dramaturgie',
                    'jobTypeMale' => 'Dramaturg',
                    'jobTypeFemale' => 'Dramaturgin',
                    'jobType' => 'Dramaturgie',
                    'title' => $this->trans->trans("default.landingpage.title.dramaturgie", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.dramaturgie", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.dramaturgie", array(), 'messages')
                ];
                break;
            case 'kbb':
                $options = [
                    'subCategoryIds' => [69],
                    'categoryName' => 'KBB',
                    'jobTypeMale' => 'Disponent',
                    'jobTypeFemale' => 'Disponentin',
                    'jobType' => 'KBB',
                    'title' => $this->trans->trans("default.landingpage.title.kbb", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.kbb", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.kbb", array(), 'messages')
                ];
                break;
            case 'korrepetitor':
                $options = [
                    'subCategoryIds' => [17],
                    'categoryName' => 'Korrepetition',
                    'jobTypeMale' => 'Korrepetitor',
                    'jobTypeFemale' => 'Korrepetitorin',
                    'jobType' => 'Korrepetitions',
                    'title' => $this->trans->trans("default.landingpage.title.korrepetition", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.korrepetition", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.korrepetition", array(), 'messages')
                ];
                break;
            case 'maskenbildner':
                $options = [
                    'subCategoryIds' => [48],
                    'categoryName' => 'Maskenbild',
                    'jobTypeMale' => 'Maskenbildner',
                    'jobTypeFemale' => 'Maskenbildnerin',
                    'jobType' => 'Maskenbild',
                    'title' => $this->trans->trans("default.landingpage.title.maskenbild", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.maskenbild", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.maskenbild", array(), 'messages')
                ];
                break;
            case 'requisiteur':
                $options = [
                    'subCategoryIds' => [41],
                    'categoryName' => 'Requisiten',
                    'jobTypeMale' => 'Requisiteur',
                    'jobTypeFemale' => 'Requisiteurin',
                    'jobType' => 'Requisiten',
                    'title' => $this->trans->trans("default.landingpage.title.requisiten", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.requisiten", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.requisiten", array(), 'messages')
                ];
                break;
            case 'tontechniker':
                $options = [
                    'subCategoryIds' => [55],
                    'categoryName' => 'Videotechnik',
                    'jobTypeMale' => 'Tontechniker',
                    'jobTypeFemale' => 'Tontechnikerin',
                    'jobType' => 'Videotechnik',
                    'title' => $this->trans->trans("default.landingpage.title.videotechnik", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.videotechnik", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.videotechnik", array(), 'messages')
                ];
                break;
            case 'veranstaltungstechniker':
                $options = [
                    'subCategoryIds' => [60],
                    'categoryName' => 'Veranstaltungstechnik',
                    'jobTypeMale' => 'Veranstaltungstechniker',
                    'jobTypeFemale' => 'Veranstaltungstechnikerin',
                    'jobType' => 'Veranstaltungstechnik',
                    'title' => $this->trans->trans("default.landingpage.title.veranstaltungstechnik", array(), 'messages'),
                    'description' => $this->trans->trans("default.landingpage.description.veranstaltungstechnik", array(), 'messages'),
                    'keywords' => $this->trans->trans("default.landingpage.keywords.veranstaltungstechnik", array(), 'messages')
                ];
                break;
            default:
                throw $this->createNotFoundException('Unable to find Category');
        }
        return $options;
    }

    /**
     * The goodbyeAction
     *
     * The Site to be shown after logout.
     * @return array
     * @internal param Request $request
     *
     * @Route("/goodbye", name="tj_main_default_goodby", options={"i18n" = false})
     */
    public
    function goodbyeAction()
    {
        return $this->render('TheaterjobsMainBundle:Default:goodbye.html.twig', array());
    }

    /**
     * The goodbyeAction
     *
     * The Site to be shown after logout.
     * @Route("/maintenance", name="tj_main_default_maintenance", options={"i18n" = false})
     */
    public function maintenanceAction()
    {
        return $this->render('TheaterjobsMainBundle:Default:maintenance.html.twig');
    }

    /**
     * @return Response
     * @internal param Request $request
     *
     * @Route("/login-modal", name="tj_main_default_login_modal" , condition="request.isXmlHttpRequest()", options={"i18n" = false})
     */
    public function loginModalAction()
    {
        return $this->render('TheaterjobsMainBundle:Default:loginModal.html.twig', []);
    }

    /**
     * @return Response
     * @internal param Request $request
     * @Method("GET")
     * @Route("/user-modal", name="tj_main_default_user_modal", options={"expose"=true, "i18n" = false}, condition="request.isXmlHttpRequest()")
     * @Security("is_granted('ROLE_USER')")
     */
    public function userModalAction()
    {
        $user = $this->getUser();
        $profile = $user->getProfile();
        $fes = $this->get('fos_elastica.manager');

        //Total jobs of a specified user
        $query = $fes->getRepository('TheaterjobsInserateBundle:Job')->userJobs($user->getId());
        $query->setSize(0);
        $userJobs = $this->get('fos_elastica.index.theaterjobs.job')->search($query)->getTotalHits();

        //Total Notifications of a user
        $query = $fes->getRepository('TheaterjobsUserBundle:Notification')->allUnseenNotifications($user->getId());
        $notifications = $this->container->get('fos_elastica.index.events.notification')->search($query)->getTotalHits();

        //count applied jobs
        $query = $this->get('fos_elastica.manager')->getRepository(ApplicationTrack::class)->countAppliedJobs($profile->getId());
        $nrAppliedJobs = $this->get('fos_elastica.index.theaterjobs.application_track')->search($query)->getTotalHits();

        return $this->render('TheaterjobsMainBundle:Default:userModal.html.twig', [
            'profile' => $profile,
            'userJobs' => $userJobs,
            'nrUnseenNot' => $notifications,
            'nrAppliedJobs' => $nrAppliedJobs,
            'nrSaveSearches' => $profile->getSearches()->count()
        ]);
    }

    /**
     *
     * @Route("/actuality-modal", name="tj_main_default_actuality_modal", options={"i18n" = false}, condition="request.isXmlHttpRequest()")
     * @Security("is_granted('ROLE_USER')")
     * @return Response
     */
    public function actualityModalAction()
    {
        $profile = $this->getProfile();
        $slug = $profile->getSlug();
        $form = $this->createCreateForm(ActualityType::class, $profile, [], 'tj_profile_user_actuality', ['slug' => $slug]);
        return $this->render('TheaterjobsProfileBundle:Modal:actuality.html.twig', [
            'profile' => $this->getProfile(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @return Response
     * @internal param Request $request
     *
     * @Route("/reset-modal", name="tj_main_default_reset_modal", options={"i18n" = false})
     */
    public function resetModalAction()
    {
        return $this->render('TheaterjobsMainBundle:Default:resetModal.html.twig', []);
    }

    /**
     * The goodbyeAction
     *
     * The Site to be shown after logout.
     * @return Response
     * @internal param Request $request
     *
     * @Route("/register-account", name="tj_main_register", options={"i18n" = false})
     */
    public function registerAction()
    {
        return $this->render('TheaterjobsMembershipBundle:Membership:index.html.twig', []);
    }


    /**
     * The termsAndTradesAction
     *
     * The Site to be shown our terms and trades.
     * @return Response
     * @internal param Request $request
     *
     * @Route("/terms-and-trades", name="tj_main_default_terms_new_tab", options={"expose"=true})
     * @Method({"GET"})
     */
    public function termsAndTradesAction()
    {
        $title = $this->trans->trans("default.terms.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.terms.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.terms.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        return $this->render('TheaterjobsMainBundle:Default:termsAndTrades.html.twig', []);
    }

    /**
     * @Route("/about-us", name="tj_main_default_about_us")
     */
    public function aboutUsAction()
    {
        $title = $this->trans->trans("default.about.us.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.about.us.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.about.us.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $about = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 1, 'deletedAt' => null]);

        return $this->render('TheaterjobsMainBundle:Default:aboutUs.html.twig', ['about' => $about]);
    }

    /**
     * @Route("/contact-site", name="tj_main_default_contact_site")
     */
    public function contactSiteAction()
    {
        $title = $this->trans->trans("default.contact.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.contact.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.contact.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $contactSite = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 1, 'deletedAt' => null]);

        return $this->render('TheaterjobsMainBundle:Default:contactSite.html.twig', ['contactSite' => $contactSite]);
    }


    /**
     * @Route("/kiba", name="tj_main_default_kiba_site", options={"i18n" = false})
     */
    public function kibaSiteAction()
    {
        $title = $this->trans->trans("default.kiba.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.kiba.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.kiba.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        return $this->render('TheaterjobsMainBundle:Default:kibaSite.html.twig');
    }


    /**
     * @Route("/contact-us", name="tj_main_default_contact_us")
     */
    public function contactUsAction()
    {
        $contact = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 2, 'deletedAt' => null]);

        return $this->render('TheaterjobsMainBundle:Default:contactUs.html.twig', ['contact' => $contact]);
    }

    /**
     * @Route("/impresum", name="tj_main_default_impresum")
     */
    public function imprintAction()
    {
        $title = $this->trans->trans("default.imprint.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.imprint.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.imprint.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $impresum = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 3, 'deletedAt' => null]);
        return $this->render('TheaterjobsMainBundle:Default:imprint.html.twig', ['impresum' => $impresum]);
    }

    /**
     * @Route("/prices", name="tj_main_default_prices")
     */
    public function pricesAction()
    {
        $title = $this->trans->trans("default.prices.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.prices.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.prices.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $prices = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 4, 'deletedAt' => null]);

        return $this->render('TheaterjobsMainBundle:Default:prices.html.twig', ['prices' => $prices]);
    }

    /**
     * @Route("/privacy", name="tj_main_default_privacy", options={"expose"=true})
     */
    public function privacyAction()
    {
        $title = $this->trans->trans("default.privacy.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->trans->trans("default.privacy.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->trans->trans("default.privacy.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $privacy = $this->getEM()->getRepository('TheaterjobsMainBundle:SiteInfo')->findBy(['type' => 5, 'deletedAt' => null]);

        return $this->render('TheaterjobsMainBundle:Default:privacy.html.twig', ['privacy' => $privacy]);

    }

    /**
     * @Route("/gratification", name="tj_gratifiction_explanation_page", options={"expose"=true, "i18n" = false})
     */
    public function gratificationExplanationAction()
    {
        $gratification = $this->getEM()->getRepository('TheaterjobsInserateBundle:Gratification')->findAll();

        return $this->render("TheaterjobsMainBundle:Default:gratificationExplanation.html.twig", [
            'gratification' => $gratification
        ]);
    }

    /**
     * @Route("/autosuggestion/languages", name="languages_autosuggestion", options={"expose"=true, "i18n" = false})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestLangAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $title = $request->query->get('q', '');
        $skills = $em->getRepository(Skill::class)->getOtherSkill($title, true);
        $results = [];

        $pagination = $this->paginator->paginate(
            $skills, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        foreach ($pagination as $node) {
            $results[] = [
                'id' => $node->getTitle(),
                'text' => $node->getTitle(),
                'total_count' => $pagination->getTotalItemCount()
            ];
        }

        return new JsonResponse($results);
    }


    /**
     * @TODO Move to a dedicated controller
     * @Route("/creators/autosuggestion", name="creators_autosuggestion", options={"expose"=true, "i18n" = false})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestCreatorAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('q', '');
        $newCheck = $request->query->getBoolean('newCheck', false);
        $repo = $em->getRepository('TheaterjobsProfileBundle:Creator');
        $creators = $repo->creatorAutosuggestion($name);
        $results = [];

        $pagination = $this->paginator->paginate(
            $creators, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        // ids of roots that are already seen
        $rootSeen = [];
        foreach ($pagination as $node) {
            // get the root node
            $root = $node->getRoot();

            // check if root is already seen so we skip the loop
            if (!in_array($root->getId(), $rootSeen)) {
                // getting all the siblings from the root node
                $siblingsByRoot = $repo->getSiblingsByRoot($root);

                foreach ($siblingsByRoot as $sibling) {
                    $results[] = [
                        'id' => $sibling->getName(),
                        'text' => $sibling->getName(),
                        'disabled' => $newCheck,
                        'total_count' => $pagination->getTotalItemCount()
                    ];
                }
                if (!in_array($root->getId(), $rootSeen)) {
                    $rootSeen[] = $root->getId();
                }
            }
        }

        $results = array_values(array_unique($results, SORT_REGULAR));

        return new JsonResponse($results);
    }

    /**
     * @TODO Move to a dedicated controller
     * @Route("/directors/autosuggestion", name="directors_autosuggestion", options={"expose"=true, "i18n" = false})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestDirectorsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('q', '');
        $newCheck = $request->query->getBoolean('newCheck', false);
        $repo = $em->getRepository('TheaterjobsProfileBundle:Director');
        $directors = $repo->directorAutosuggestion($name);
        $results = [];

        $pagination = $this->paginator->paginate(
            $directors, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        // ids of roots that are already seen
        $rootSeen = [];
        foreach ($pagination as $node) {
            // get the root node
            $root = $node->getRoot();

            // check if root is already seen so we skip the loop
            if (!in_array($root->getId(), $rootSeen)) {
                // getting all the siblings from the root node
                $siblingsByRoot = $repo->getSiblingsByRoot($root);

                foreach ($siblingsByRoot as $sibling) {
                    $results[] = [
                        'id' => $sibling->getName(),
                        'text' => $sibling->getName(),
                        'disabled' => $newCheck,
                        'total_count' => $pagination->getTotalItemCount()
                    ];
                }
                if (!in_array($root->getId(), $rootSeen)) {
                    $rootSeen[] = $root->getId();
                }
            }
        }

        return new JsonResponse(array_values(array_unique($results, SORT_REGULAR)));
    }

    /**
     * @Route("/general-search", name="tj_main_search", options={"expose"=true, "i18n" = false})
     * @param Request $request
     * @return bool|JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $search = $request->get('search', '');
        $fosElastica = $this->container->get('fos_elastica.manager');
        $peopleFinder = $this->container->get('fos_elastica.finder.theaterjobs.profile');
        $newFinder = $this->container->get('fos_elastica.finder.theaterjobs.news');
        $organizationFinder = $this->container->get('fos_elastica.finder.theaterjobs.organization');
        $jobFinder = $this->container->get('fos_elastica.finder.theaterjobs.job');

        $queryOrganization = $fosElastica->getRepository('TheaterjobsInserateBundle:Organization')->generalSearch($search, $isAdmin);
        $resultsOrganizations = $organizationFinder->createPaginatorAdapter($queryOrganization);
        // show only 3 results
        $organizations = $this->paginator->paginate($resultsOrganizations, 1, 3);

        //If the user is not registered or is not a member than we won't display any results so we don't run this query.
        //This is also handled in the view so it will not give any errors.
        if ($this->isGranted('ROLE_MEMBER')) {
            $queryJob = $fosElastica->getRepository('TheaterjobsInserateBundle:Job')->generalSearch($search);
            $resultsJobs = $jobFinder->createPaginatorAdapter($queryJob);
            // show only 3 results
            $jobs = $this->paginator->paginate($resultsJobs, 1, 3);
        } else {
            $jobs = [];
        }


        $queryNews = $fosElastica->getRepository('TheaterjobsNewsBundle:News')->generalSearch($search, $isAdmin);
        $resultsNews = $newFinder->createPaginatorAdapter($queryNews);
        // show only 3 results
        $news = $this->paginator->paginate($resultsNews, 1, 3);

        $queryProfile = $fosElastica->getRepository('TheaterjobsProfileBundle:Profile')->generalSearch($search);
        $resultsPeoples = $peopleFinder->createPaginatorAdapter($queryProfile);
        // show only 3 results
        $peoples = $this->paginator->paginate($resultsPeoples, 1, 3);

        return $this->render('TheaterjobsMainBundle:Partial:search.html.twig', [
                'peoples' => $peoples,
                'organizations' => $organizations,
                'news' => $news,
                'jobs' => $jobs,
                'search' => $search
            ]
        );
    }

    /**
     * get $nr latest news
     * @param $nr
     * @return array
     */
    public function getLatestNews($nr)
    {
        $finder = $this->get('fos_elastica.finder.theaterjobs.news');
        $query = $this->get('fos_elastica.manager')->getRepository(News::class)->latestNews();
        return $finder->find($query, $nr);
    }

    /**
     * get $nr latest news
     * @param $nr
     * @return array
     */
    public function getLatestJobs($nr)
    {
        $finder = $this->get('fos_elastica.finder.theaterjobs.job');
        $query = $this->get('fos_elastica.manager')->getRepository(Job::class)->latestJobs();
        return $finder->find($query, $nr);
    }

    /**
     * Get random images
     * @param $limit
     * @return array
     */
    public function getRamdomImages($limit)
    {
        $finder = $this->get('fos_elastica.finder.theaterjobs.profile');
        $query = $this->get('fos_elastica.manager')->getRepository(Profile::class)->randomProfiles();
        return $finder->find($query, $limit);
    }
}