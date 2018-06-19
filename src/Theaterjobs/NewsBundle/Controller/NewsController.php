<?php

namespace Theaterjobs\NewsBundle\Controller;

use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\NewsBundle\Entity\Replies;
use Theaterjobs\NewsBundle\Entity\Tags;
use Theaterjobs\NewsBundle\Form\NewsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\NewsBundle\Form\RepliesType;
use Symfony\Component\HttpFoundation\Session\Session;
use Carbon\Carbon;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Theaterjobs\NewsBundle\Model\NewsSearch;

/**
 * News controller.
 *
 * @Route("/")
 */
class NewsController extends BaseController
{
    use StatisticsTrait;
    use ESUserActivity;

    /** @DI\Inject("knp_paginator") */
    private $paginator;

    /**
     * @DI\Inject("%theaterjobs_news.category.news.root_slug%")
     */
    protected $newscategoryRoot;


    /**
     * @DI\Inject("sonata.seo.page")
     * @var SeoPage
     */
    private $seo;

    /**
     * Lists all News entities.
     *
     * @Route("/index", name="tj_news", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse|Response|UnauthorizedHttpException
     */
    public function indexAction(Request $request)
    {
        $this->seo->setTitle($this->getTranslator()->trans("default.newsIndex.title", [], 'messages'));
        $description = $this->getTranslator()->trans("default.newsIndex.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->getTranslator()->trans("default.newsIndex.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $isAjax = $request->isXmlHttpRequest();
        $isAnon = $this->isAnon();
        $em = $this->getEM();

        $newsSearch = new NewsSearch();

        $newsSearchForm = $this->createGeneralSearchForm('news_search_type',
            $newsSearch,
            [
                'role' => $this->isGranted('ROLE_ADMIN'),
                'news_years_range' => $this->container->getParameter('news_years_range')
            ],
            'tj_news'
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $newsSearch);
        $newsSearchForm->handleRequest($request);
        $newsSearch = $newsSearchForm->getData();
        if ($isAnon) {
            $newsSearch->setFavorite(0);
            $newsSearch->setPublished(1);
        }
        if ($newsSearch->isFavorite()) {
            $newsSearch->setNewsFavourites($this->getProfile()->getNewsFavouriteIds());
        }

        $result = $this->container->get('fos_elastica.index.theaterjobs.news');

        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsNewsBundle:News')->search($newsSearch);
        $page = $request->query->get('page', 1);
        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query,
                [],
                new ElasticaToRawTransformer()
            ),
            $page, $this->container->getParameter('pagination')
        );
        $latestReplies = $em->getRepository('TheaterjobsNewsBundle:News')->findTenLatestComments();

        $content = $this->render(
            $isAjax ? 'TheaterjobsNewsBundle:Partial:news.html.twig' : 'TheaterjobsNewsBundle:News:index.html.twig',
            [
                'latestReplies' => $latestReplies,
                'news' => $pagination,
                'form' => $newsSearchForm->createView(),
            ]);

        return $isAjax ? $this->generalCustomCacheControlDirective([
            'html' => $content->getContent()
        ]) : $content;

    }


    /**
     * Displays a form to create a new News entity.
     *
     * @Route("/new", name="tj_news_new", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('create_news')")
     */
    public function newAction()
    {

        $entity = new News();
        $form = $this->createCreateForm(NewsType::class, $entity, $options = [], 'tj_news_create');

        return $this->render('TheaterjobsNewsBundle:Modal:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Creates a new News entity.
     *
     * @Route("/create", name="tj_news_create", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('create_news')")
     */
    public function createAction(Request $request)
    {

        $entity = new News();
        $em = $this->getEM();
        $form = $this->createCreateForm(NewsType::class, $entity, $options = [], 'tj_news_create');
        $form->handleRequest($request);

        if ($form->isValid()) {

            $numArray = explode(",", $form["tags_helper"]->getData());
            foreach ($numArray as $num) {
                if (strlen($num) > 0)
                    $entity->addTag($this->handleTags($num));
            }
            $organizationNumArray = explode(",", $form["organizations_helper"]->getData());
            foreach ($organizationNumArray as $num) {
                if (strlen($num) > 0)
                    $entity->addOrganization($this->handleOrganizations($num));
            }

            $entity->setCreatedBy($this->getProfile());
            $em->persist($entity);
            $em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($entity, $this->getTranslator()->trans("tj.user.activity.news.created", [], 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $msg = $this->getTranslator()->trans("flash.success.news.created", ['%newstitle%' => $entity->getTitle()], 'flashes');
            $this->addFlash('newsShow', ['success' => $msg]);
            //it's used on ServiceMenu.php on MainBundle to check if an adminstrator has news
            $request->getSession()->set('myNews', true);

            return new JsonResponse([
                'success' => 1,
                'redirect' => $this->generateUrl('tj_news_show', ['slug' => $entity->getSlug()])
            ]);
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(
                [
                    'errors' => $this->getErrorMessages($form)
                ]
            );
        }

        return $this->render('TheaterjobsNewsBundle:News:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Displays a form to edit an existing News entity.
     *
     * @Route("/{slug}/edit", name="tj_news_edit", options={"expose"=true} , condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param News $news
     * @return Response
     * @internal param $slug
     * @Security("is_granted('edit_news')")
     */
    public function editAction(News $news)
    {
        $tags = $news->getTags();
        $organizations = $news->getOrganizations();
        $tags_titles = '';
        $organizations_titles = '';
        $users = $news->getUsers();
        $existingUsers = [];
        $src = null;
        foreach ($tags as $tag) {
            $tags_titles .= $tag->getTitle() . ',';
        }
        $tags_titles = substr_replace($tags_titles, "", -1);

        foreach ($organizations as $organization) {
            $organizations_titles .= $organization->getName() . ',';
        }
        $organizations_titles = substr_replace($organizations_titles, "", -1);

        //building the format of select2 for the existing users
        if ($users) {
            foreach ($users as $user) {
                $existingUsers[] = ['id' => $user->getId(), 'text' => $user->getFullName()];
            }
        }

        $editForm = $this->createEditForm(NewsType::class, $news, [], 'tj_news_update', ['slug' => $news->getSlug()]);

        return $this->render('TheaterjobsNewsBundle:Modal:edit.html.twig', array(
                'entity' => $news,
                'form' => $editForm->createView(),
                'tag_titles' => $tags_titles,
                'organization_titles' => $organizations_titles,
                'existing_users' => $existingUsers ? $existingUsers : null
            )
        );
    }

    /**
     * Displays a form to edit an existing News entity.
     *
     * @Route("/ckeditor/upload", name="tj_news_ckeditor_upload", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function ckeditorUploadAction(Request $request)
    {
        $file = $request->files->get('upload');
        $absolutePath = __DIR__ . '/../../../../web/uploads/ckeditor';
        $file->move($absolutePath, $file->getClientOriginalName());
        $url = $this->get('templating.helper.assets')->getUrl('uploads/ckeditor/' . $file->getClientOriginalName());
        $funcNum = $request->query->get('CKEditorFuncNum');
        $message = '';
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";

    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/show/{slug}", name="tj_news_show", options={"expose"=true})
     * @Method("GET")
     * @param News $news
     * @return Response
     * @internal param $slug
     * @Security("is_granted('view_news', news)")
     */
    public function showAction(News $news)
    {
        // initialize vars
        $canComment = false;
        $formComment = null;
        $weeklyStats = null;
        $monthlyStats = null;
        $allStats = null;

        $isAnon = $this->isAnon();
        $isAdmin = $this->isGranted("ROLE_ADMIN");
        $em = $this->getEM();


        if (!$isAnon) {
            $canComment = $this->getProfile()->getProfileAllowedTo()->getCommentInNews();
        }

        // Mark as viewed
        $this->viewEvent(News::class, $news->getId(), $this->getUser());
        $this->seo->setTitle(sprintf('%s-Theapolis', $news->getTitle()))
            ->addMeta('name', 'description', $news->getShortDescription())
            ->addMeta('name', 'keywords', $this->get('translator')->trans("seo.news.keywords", [], 'seo'));

        // View params
        if ($canComment) {
            $formComment = $this->createCommentForm($news)->createView();
        }

        if ($isAdmin) {
            $weeklyStats = $this->countWeeklyViews(News::class, $news->getId());
            $monthlyStats = $this->countMonthlyViews(News::class, $news->getId());
            $allStats = $this->countAllViews(News::class, $news->getId());
        }

        $comments = $em->getRepository(News::class)->getCommentsByNews($news);

        return $this->render('TheaterjobsNewsBundle:News:show.html.twig', [
                'entity' => $news,
                'anon' => $isAnon,
                'commentForm' => $formComment,
                'comments' => $comments,
                'weeklyStats' => $weeklyStats,
                'monthlyStats' => $monthlyStats,
                'allStats' => $allStats,
                'creator' => $news->getCreatedBy(),
            ]
        );
    }

    public function returnPartial(News $news)
    {
        $em = $this->getEM();
        $comments = $em->getRepository('TheaterjobsNewsBundle:News')->getCommentsByNews($news, $em->createQueryBuilder());

        return $this->render('TheaterjobsNewsBundle:Partial:newsComments.html.twig', array(
            'comments' => $comments,
            'entity' => $news->getSlug()
        ))->getContent();
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/all/comments/{slug}", name="tj_news_comments_all", options={"expose"=true})
     * @Method({"GET"})
     * @param $slug
     * @return Response
     */
    public function getAllComments($slug)
    {

        $em = $this->getEM();
        $entity = $em->getRepository('TheaterjobsNewsBundle:News')->findOneBy(array('slug' => $slug));
        $comments = $em->getRepository('TheaterjobsNewsBundle:News')->getCommentsByNews($entity, $em->createQueryBuilder());

        return $this->render('TheaterjobsNewsBundle:Modal:showAll.html.twig', array(
            'comments' => $comments
        ));
    }

    /**
     * @Route("/addnewsfavourite/{slug}", name="tj_news_favourite_root", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param News $news
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("has_role('ROLE_MEMBER') or has_role('ROLE_ADMIN')")
     */
    public function addNewsToFavouritesAction(Request $request, News $news)
    {
        if ($request->isXmlHttpRequest()) {
            $authenticated_profile = $this->getUser()->getProfile();
            $status = [
                'status' => 'ERROR'
            ];

            $em = $this->getEM();

            if (!$authenticated_profile->getNewsFavourite()->contains($news)) {
                $authenticated_profile->addNewsFavourite($news);

                $em->persist($authenticated_profile);
                $em->flush();
                $status['status'] = "SUCCESS";
            }

            return new JsonResponse($status);
        }
        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * @Route("/remove-news-favourite/{slug}", name="tj_news_favourite_remove", options={"expose"=true})
     * @Method("GET")
     * @param News $news
     * @return JsonResponse
     */
    public function removeNewsToFavouritesAction(News $news)
    {
        $authenticated_profile = $this->getUser()->getProfile();

        $status = [
            'status' => 'ERROR'
        ];

        $em = $this->getEM();

        if ($authenticated_profile->getNewsFavourite()->contains($news)) {
            $authenticated_profile->removeNewsFavourite($news);

            $em->persist($authenticated_profile);
            $em->flush();
            $status['status'] = "SUCCESS";
        } else {
            dump($authenticated_profile);
            die;
        }

        return new JsonResponse($status);
    }


    /**
     * @Route("/remove-news-favourite-list/{slug}", name="tj_news_favourite_list_remove")
     * @Method("GET")
     * @param News $news
     * @return mixed
     */
    public function removeNewsToFavouritesListAction(News $news)
    {
        $authenticated_profile = $this->getUser()->getProfile();
        $em = $this->getEM();

        if ($authenticated_profile->getNewsFavourite()->contains($news)) {
            $authenticated_profile->removeNewsFavourite($news);
            $em->persist($authenticated_profile);
            $em->flush();
        }

        if (count($this->getUser()->getProfile()->getNewsFavourite()) > 0)
            return $this->redirect($this->generateUrl('tj_news', array('favourite' => 1)));
        else
            return $this->redirect($this->generateUrl('tj_news'));


    }


    /**
     * Edits an existing News entity.
     *
     * @Route("/update/{slug}", name="tj_news_update", options={"expose"=true})
     * @Method("PUT")
     * @param Request $request
     * @param News $news
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @internal param $slug
     * @Security("is_granted('edit_news')")
     */
    public function updateAction(Request $request, News $news)
    {
        $em = $this->getEM();

        $editForm = $this->createEditForm(NewsType::class, $news, [], 'tj_news_update', ['slug' => $news->getSlug()]);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            foreach ($news->getTags() as $oldTag)
                $news->removeTag($oldTag);

            foreach ($news->getOrganizations() as $oldOrganization)
                $news->removeOrganization($oldOrganization);

            $numArray = explode(",", $editForm["tags_helper"]->getData());
            foreach ($numArray as $num) {
                if (strlen($num) > 0)
                    $news->addTag($this->handleTags($num));
            }

            $organizationNumArray = explode(",", $editForm["organizations_helper"]->getData());
            foreach ($organizationNumArray as $num) {
                if (strlen($num) > 0)
                    $news->addOrganization($this->handleOrganizations($num));
            }

            $em->persist($news);
            $em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($news, $this->getTranslator()->trans("tj.user.activity.news.updated", [], 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            $this->addFlash('newsShow', ['success' => $this->getTranslator()->trans("flash.success.news.updated", ['%newstitle%' => $news->getTitle()], 'flashes')]);
            return $this->redirect($this->generateUrl('tj_news_show', array('slug' => $news->getSlug())));
        }

        return $this->render('TheaterjobsNewsBundle:News:edit.html.twig', array(
                'entity' => $news,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * List news by category
     *
     * @param $category
     * @param int $page
     * @return Response $array
     *
     * @Route("/index/{category}/category", name="tj_news_index_category")
     * @Route("/index/{category}/category/{page}", name="tj_news_index_category_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function newsByCategoryAction($category, $page = 1)
    {
        $isAjax = $this->get('request')->isXmlHttpRequest();
        $date = Carbon::now();
        $entities = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findPublishedNewsByCategory($date, $category);

        $pagination = $this->paginator->paginate(
            $entities, $page/* page number */, 10/* limit per page */
        );
        $categories = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->populateCategories($this->getEM()->createQueryBuilder(), $date);
        if ($isAjax) {
            $template = null;
            if (count($pagination)) {
                $template = $this->renderView('TheaterjobsNewsBundle:News:_newsList.html.twig', array('entities' => $pagination));
            }
            return new Response($template);
        }
        $latestReplies = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findTenLatestComments($this->getEM()->createQueryBuilder(), $date);
        return $this->render('TheaterjobsNewsBundle:News:index.html.twig', array(
                'entities' => $pagination,
                'categories' => $categories,
                'currentCategory' => $category,
                'latestReplies' => $latestReplies
            )
        );
    }

    /**
     * List news by category
     *
     * @param Organization $organization
     * @param int $page
     * @return Response $array
     *
     * @Route("/index/{organization}/organization", name="tj_news_index_organization")
     * @Route("/index/{organization}/organization/{page}", name="tj_news_index_organization_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function newsByOrganizationAction(Organization $organization, $page = 1)
    {
        $isAjax = $this->get('request')->isXmlHttpRequest();
        $date = Carbon::now();
        $entities = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findPublishedNewsByOrganization($date, $organization);

        $pagination = $this->paginator->paginate(
            $entities, $page/* page number */, 10/* limit per page */
        );
        $categories = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->populateCategories($this->getEM()->createQueryBuilder(), $date);
        if ($isAjax) {
            $template = null;
            if (count($pagination)) {
                $template = $this->renderView('TheaterjobsNewsBundle:News:_newsList.html.twig', array('entities' => $pagination));
            }
            return new Response($template);
        }
        $latestReplies = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findTenLatestComments($this->getEM()->createQueryBuilder(), $date);
        return $this->render('TheaterjobsNewsBundle:News:index.html.twig', array(
                'entities' => $pagination,
                'categories' => $categories,
                'organization' => $organization,
                'latestReplies' => $latestReplies
            )
        );
    }

    /**
     * List news by tag
     *
     * @param $tag
     * @param int $page
     * @return Response $array
     *
     * @Route("/index/{tag}/tag", name="tj_news_index_tag", options={"expose"=true})
     * @Route("/index/{tag}/tag/{page}", name="tj_news_index_tag_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function newsByTagAction($tag, $page = 1)
    {
        $isAjax = $this->get('request')->isXmlHttpRequest();
        $date = Carbon::now();
        $entities = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findPublishedNewsByTag($date, $tag);

        $categories = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->populateCategories($this->getEM()->createQueryBuilder(), $date);
        $pagination = $this->paginator->paginate(
            $entities, $page/* page number */, 10/* limit per page */
        );
        if ($isAjax) {
            $template = null;
            if (count($pagination)) {
                $template = $this->renderView('TheaterjobsNewsBundle:News:_newsList.html.twig', array(
                        'entities' => $pagination)
                );
            }
            return new Response($template);
        }
        $latestReplies = $this->getEM()->getRepository('TheaterjobsNewsBundle:News')->findTenLatestComments($this->getEM()->createQueryBuilder(), $date);

        $tagEntity = $this->getEM()->getRepository('TheaterjobsNewsBundle:Tags')->findOneBy(array('id' => $tag));

        return $this->render('TheaterjobsNewsBundle:News:index.html.twig', array(
                'entities' => $pagination,
                'categories' => $categories,
                'tag' => $tagEntity,
                'latestReplies' => $latestReplies,
            )
        );
    }

    /**
     * Creates a form to add comment on news.
     *
     * @param News $news
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCommentForm(News $news)
    {
        $entity = new Replies();
        $form = $this->createForm(RepliesType::class, $entity, array(
            'action' => $this->generateUrl('tj_news_add_comment', array('slug' => $news->getSlug())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->getTranslator()->trans('button.news.comment')));

        return $form;
    }

    /**
     * Finds and displays latest news
     *
     * @Route("/latestnews/", name="tj_news_latest_news", options={"expose"=true})
     * @Method("GET")
     */
    public
    function showNewTopicsAction()
    {
        $em = $this->getEM();
        $session = new Session();
        $date_from = $session->get('lastLogin');
        $date = Carbon::now();
        $news = $em->getRepository('TheaterjobsNewsBundle:News')->latestNews($date_from, $date);
        $categories = $em->getRepository('TheaterjobsNewsBundle:News')->populateCategories($em->createQueryBuilder(), $date);
        return $this->render('TheaterjobsNewsBundle:News:index.html.twig', array(
                'entities' => $news,
                'categories' => $categories
            )
        );
    }

    /**
     * @Route("/delete/logo/{slug}", name="tj_news_delete_logo", options={"expose"=true})
     * @Method("GET")
     * @param News $news
     * @return JsonResponse
     */
    public function deleteImageAction(News $news)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $path = $helper->asset($news, 'uploadFile');
        $absolutePath = __DIR__ . '/../../../../web' . $path;
        unlink($absolutePath);
        $news->setPath(NULL);
        $news->setImageDescription(NULL);
        $news->setTemp($path);
        $news->setUpdatedAt(new \DateTime());
        $this->getEM()->persist($news);
        $this->getEM()->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/get/tags", name="tj_news_get_tags", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function tagSuggestAction(Request $request)
    {
        $em = $this->getEM();
        $repo = $em->getRepository('TheaterjobsNewsBundle:Tags');
        $tags = $repo->tagSuggest($request->query->get('q'));

        $pagination = $this->paginator->paginate(
            $tags, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );

        $response = [];


        foreach ($pagination as $t) {
            $tag['id'] = $t->getId();
            $tag['text'] = $t->getTitle();
            $tag['total_count'] = $pagination->getTotalItemCount();
            $response[] = $tag;
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/get/organizations", name="tj_news_get_organizations", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function organizationsSuggestAction(Request $request)
    {
        $response = array();
        $search = $request->query->get('q');
        $result = $this->container->get('fos_elastica.index.theaterjobs.organization');
        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Organization')->generalSearch($search);

        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query,
                [],
                new ElasticaToRawTransformer()
            ),
            $request->query->getInt('page', 1),
            $this->container->getParameter('autosuggestion_pagination')
        );

        foreach ($pagination as $o) {
            $organization['id'] = $o->id;
            $organization['text'] = $o->name;
            $organization['total_count'] = $pagination->getTotalItemCount();
            $response[] = $organization;
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/get/users", name="tj_news_get_users", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function usersSuggestAction(Request $request)
    {

        $search = $request->query->get('q');
        $result = $this->container->get('fos_elastica.index.theaterjobs.profile');

        $isPublished = $this->isGranted('ROLE_ADMIN');
        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsProfileBundle:Profile')->generalSearch($search, $isPublished);
        $response = [];

        $pagination = $this->paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query,
                [],
                new ElasticaToRawTransformer()
            ),
            $request->query->getInt('page', 1),
            $this->container->getParameter('autosuggestion_pagination')
        );

        foreach ($pagination as $p) {
            $user['id'] = $p->id;
            $user['text'] = $p->firstName . ' ' . $p->lastName;
            $user['total_count'] = $pagination->getTotalItemCount();
            $response[] = $user;
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/get/tags-search", name="tj_news_get_tags_search", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function tagSuggestActionSearch(Request $request)
    {
        $tags = $this->getEM()->getRepository('TheaterjobsNewsBundle:Tags')->tagSuggest($request->query->get('q'));
        $response = [];

        $pagination = $this->paginator->paginate(
            $tags, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->container->getParameter('autosuggestion_pagination')/*limit per page*/
        );
        foreach ($pagination as $t) {
            $tag['id'] = $t->getTitle();
            $tag['text'] = $t->getTitle();
            $tag['total_count'] = $pagination->getTotalItemCount();
            $response[] = $tag;
        }

        return new JsonResponse($response);
    }

    private function handleTags($tag_text)
    {
        $em = $this->getEM();
        if (ctype_digit($tag_text)) {
            $tag = $em->getRepository('TheaterjobsNewsBundle:Tags')->find($tag_text);
            if ($tag) {
                return $tag;
            }
        } else {
            $tag = $em->getRepository('TheaterjobsNewsBundle:Tags')->findOneBy(array('title' => $tag_text));
            if (!$tag && strlen($tag_text) > 0) {
                $tag = new Tags();
                $tag->setTitle($tag_text);
            }
            return $tag;
        }
    }

    private function handleOrganizations($orga_text)
    {
        $em = $this->getEM();
        if (ctype_digit($orga_text)) {
            $tag = $em->getRepository('TheaterjobsInserateBundle:Organization')->find($orga_text);
            if ($tag) {
                return $tag;
            }
        } else {
            $tag = $em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name' => $orga_text));
            return $tag;
        }
    }


    /**
     * @Route("/{slug}/comment/add", name="tj_news_add_comment")
     * @Method("POST")
     * @param News $news
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addCommentAction(Request $request, News $news)
    {
        $isAllowed = $this->isGranted("ROLE_ADMIN") || $this->isGranted("ROLE_MEMBER");
        if ($this->isAnon() || !$this->getProfile()->getProfileAllowedTo()->getCommentInNews() || !$isAllowed) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans('tj.news.comment.notAllowed')
            ]);
        }
        $em = $this->getEM();
        $commentForm = $this->createCommentForm($news);
        $commentForm->handleRequest($request);
        // Handle Form
        if ($commentForm->isValid()) {
            $reply = $commentForm->getData();
            $reply->setNews($news);
            $reply->setProfile($this->getProfile());
            $em->persist($reply);
            $em->flush();
            $this->logUserActivity($news, $this->getTranslator()->trans("tj.user.activity.commented.on.news", [], 'activity'));
            return new JsonResponse([
                    'success' => true,
                    'data' => $this->returnPartial($news)]
            );
        } else {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getErrorMessagesAJAX($commentForm)
            ]);
        }
    }
}