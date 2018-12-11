<?php

namespace Theaterjobs\ProfileBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Exception;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Theaterjobs\CategoryBundle\Entity\Category;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\MainBundle\Utility\Traits\ReadNotificationTrait;
use Theaterjobs\ProfileBundle\Entity\BiographySection;
use Theaterjobs\ProfileBundle\Entity\ContactSection;
use Theaterjobs\ProfileBundle\Entity\EmbededVideos;
use Theaterjobs\ProfileBundle\Entity\MediaAudio;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Entity\Qualification;
use Theaterjobs\ProfileBundle\Entity\QualificationSection;
use Theaterjobs\ProfileBundle\Form\Type\ActualityType;
use Theaterjobs\ProfileBundle\Form\Type\BiographySectionType;
use Theaterjobs\ProfileBundle\Form\Type\ContactSectionType;
use Theaterjobs\ProfileBundle\Form\Type\GeoLocationSectionType;
use Theaterjobs\ProfileBundle\Form\Type\ProfileDataType;
use Theaterjobs\ProfileBundle\Form\Type\MediaAudioType;
use Theaterjobs\ProfileBundle\Form\Type\MediaImageType;
use Theaterjobs\ProfileBundle\Form\Type\ProfileSubtitleType;
use Theaterjobs\ProfileBundle\Form\Type\VideoType;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\ProfileBundle\Model\PeopleSearch;
use Theaterjobs\ProfileBundle\Form\Type\PeopleSearchType;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * Profile controller.
 *
 * @Route("/")
 */
class ProfileController extends BaseController
{
    use StatisticsTrait;
    use ReadNotificationTrait;

    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /**
     * @DI\Inject("sonata.seo.page")
     * @var SeoPageInterface
     */
    private $seo;


    /** @DI\Inject("theaterjobs_category.form.extension.choicelistfactory") */
    private $choiceListFactory;

    /** @DI\Inject("%theaterjobs_profile.category.profile.root_slug%") */
    private $profilecategoryRoot;

    /** @DI\Inject("%theaterjobs_profile.category.voice.root_slug%") */
    private $voicecategoryRoot;

    /** @DI\Inject("%theaterjobs_profile.drive.licence.root_slug%") */
    private $drivelicenceRoot;

    /**
     * @DI\Inject("knp_snappy.pdf")
     * @var Pdf
     */
    private $pdfGenerator;

    /** @DI\Inject("knp_paginator") */
    private $paginator;

    /** @DI\Inject("translator") */
    private $translator;

    /**
     * Count all profile entities by main category.
     *
     * @Route("/index", name="tj_profile_profile_index", options={"i18n" = false})
     * @Method("GET")
     */
    public function indexAction()
    {
        $title = $this->translator->trans("default.peopleIndex.title", [], 'messages');
        $this->seo->setTitle($title);
        $description = $this->translator->trans("default.peopleIndex.description", [], 'messages');
        $this->seo->addMeta('name', 'description', $description);
        $keywords = $this->translator->trans("default.peopleIndex.keywords", [], 'messages');
        $this->seo->addMeta('name', 'keywords', $keywords);

        $peopleSearch = new PeopleSearch();

        $peopleSearchForm = $this->createGeneralSearchForm('people_search_type',
            $peopleSearch,
            [
                'isAdmin' => $this->isGranted('ROLE_ADMIN'),
                'isLogged' => $this->isGranted('ROLE_USER')
            ],
            'tj_profile_profile_list'
        );

        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsProfileBundle:Profile')->getCategoriesAggregationQuery();
        $aggregations = $this->container->get('fos_elastica.index.theaterjobs.profile')->search($query)->getAggregations();

        $orderedAggregations = $this->orderAggSet($aggregations, $this->container->getParameter('people_base_categories'));

        return $this->render('TheaterjobsProfileBundle:Profile:people_category.html.twig',
            [
                'aggs' => $orderedAggregations,
                'form' => $peopleSearchForm->createView(),
            ]
        );
    }

    /**
     * List  all profile entities by search.
     *
     * @Route("/list/{category}", name="tj_profile_profile_list", defaults={"category" = null}, options={"expose"=true, "i18n" = false})
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
        $subcategories = [];
        $orderedAggregations = [];
        $peopleSearch = new PeopleSearch();

        if ($category) {
            $peopleSearch->setCategory($category);
            $subcategories = $this->em->getRepository('TheaterjobsCategoryBundle:Category')->findChoiceListBySlug(
                $this->profilecategoryRoot, $categorySlug, true
            );
        }

        $peopleSearchForm = $this->createGeneralSearchForm('people_search_type',
            $peopleSearch,
            [
                'isAdmin' => $this->isGranted('ROLE_ADMIN'),
                'isLogged' => $this->isGranted('ROLE_USER'),
                'subcategories' => $subcategories
            ],
            'tj_profile_profile_list',
            ['category' => $categorySlug]
        );

        // fetch query params if they are missing
        $this->fetchQueryParams($request, $peopleSearch);

        $peopleSearchForm->handleRequest($request);
        $peopleSearch = $peopleSearchForm->getData();

        // prevent to inject favorite filter without loggedIn
        if (!$this->isGranted('ROLE_USER')) {
            $peopleSearch->setFavorite(0);
        }


        if ($peopleSearch->isFavorite()) {
            $peopleSearch->setUserFavourites($this->getProfile()->getUserFavouritesIds());
        }


        $result = $this->container->get('fos_elastica.index.theaterjobs.profile');

        $query = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsProfileBundle:Profile')->search($peopleSearch, $subcategories);


        // Option 3b. KnpPaginator resultset
        /*page number*/
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

        if (isset($aggs['aggregations']['categories'])) {
            $orderedAggregations = $this->orderAggSet($aggs['aggregations'], $this->container->getParameter('people_base_categories'));
        }

        if (isset($aggs['aggregations']['subcategories'])) {
            $orderedAggregations = $aggs;
        }

        $content = $this->render($isAjax ? 'TheaterjobsProfileBundle:Partial:people.html.twig' : 'TheaterjobsProfileBundle:Profile:people_search_list.html.twig',
            [
                'profiles' => $pagination,
                'aggs' => $orderedAggregations,
                'category' => $category,
                'subcategories' => $subcategories,
                'form' => $peopleSearchForm->createView()
            ]
        );

        return $isAjax ? $this->generalCustomCacheControlDirective(['html' => $content->getContent()]) : $content;
    }


    /**
     * @Route("/pdf-generate/{slug}" , name="tj_profile_profile_pdfgen", options={"expose"=true, "i18n" = false})
     * @Method({"GET","POST"})
     * @param Profile $profile
     * @return BinaryFileResponse | RedirectResponse
     * @Security("is_authenticated()")
     */
    public function pdfAction(Profile $profile)
    {
        $owner = $this->getProfile()->getId() == $profile->getId();
        if (!$owner && !$profile->getIsPublished() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No permissions for this debit account');
        }

        $participationsNew = $experienceNew = [];
        $participations = $profile->getProductionParticipations();
        foreach ($participations as $participation) {
            if ($participation->getOngoing() == true) {
                $participationsNew[10000][] = $participation;
            } else {
                $participationsNew[$participation->getStart()->format('Y')][] = $participation;
            }
        }
        $experiences = $profile->getExperiences();
        foreach ($experiences as $experience) {
            if ($experience->getOngoing() == true) {
                $experienceNew[10000][] = $experience;
            } else {
                $experienceNew[$experience->getStart()->format('Y')][] = $experience;
            }
        }

        $profilePicture = $this->em->getRepository(MediaImage::class)->findOneBy([
            'profile' => $profile,
            'isProfilePhoto' => true
        ]);

        $fs = new Filesystem();
        $profileFile = './uploads/profile/' . $profile->getSlug() . '.pdf';
        $fs->remove($profileFile);
        $footerRoute = $this->generateUrl('tj_pdf_footer', [], true);

        $options = [
            'margin-top' => 10,
            'margin-right' => 0,
            'margin-bottom' => 30,
            'margin-left' => 0,
        ];

        $this->pdfGenerator->setOptions([
            'encoding' => 'UTF-8',
            'footer-html' => $footerRoute,
            'zoom' => 1.25
        ]);

        $this->pdfGenerator->generateFromHtml(
            $this->renderView('TheaterjobsProfileBundle:Profile:pdf.html.twig',
                [
                    'entity' => $profile,
                    'participations' => $participationsNew,
                    'experiences' => $experienceNew,
                    'profilePicture' => $profilePicture
                ]
            ), $profileFile, $options
        );

        return new BinaryFileResponse($profileFile);
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/get-footer", name="tj_pdf_footer", options={"i18n" = false})
     * @Method("GET")
     * @return Response
     */
    function getPdfFooter()
    {
        return $this->render('TheaterjobsProfileBundle:Profile:pdfFooter.html.twig');
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/geo-modal/{slug}", name="tj_profile_geo_modal", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method("GET")
     * @param Profile $profile
     * @return Response
     */
    public function geoModalAction(Profile $profile)
    {

        if (!$profile->getContactSection()) {
            $profileContactSection = new ContactSection();
            $profileContactSection->setProfile($profile);
            $profile->setContactSection($profileContactSection);
            $this->em->persist($profile);
        }
        $formName = GeoLocationSectionType::class;
        $params = ['slug' => $profile->getSlug()];
        $editFormContact = $this->createEditForm($formName, $profile->getContactSection(), [], 'tj_profile_geo', $params);

        return $this->render('TheaterjobsProfileBundle:Modal:map.html.twig', [
                'editFormContact' => $editFormContact->createView()
            ]
        );
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/contact-modal/{slug}", name="tj_profile_contact_modal", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method("GET")
     * @param Profile $profile
     * @return Response
     */
    public function contactModalAction(Profile $profile)
    {
        if (!$profile->getContactSection()) {
            $profileContactSection = new ContactSection();
            $profileContactSection->setProfile($profile);
            $profile->setContactSection($profileContactSection);
            $this->em->persist($profile);
        }
        $formName = ContactSectionType::class;
        $params = ['slug' => $profile->getSlug()];
        $editFormContact = $this->createEditForm($formName, $profile->getContactSection(), [], 'tj_profile_contact', $params);

        return $this->render('TheaterjobsProfileBundle:Modal:contact.html.twig', [
            'editFormContact' => $editFormContact->createView(),
            'socialSize' => count($this->em->getRepository('TheaterjobsAdminBundle:SocialMedia')->findAll())
        ]);
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/show/{slug}", name="tj_profile_profile_show", options={"expose"=true})
     * @Method("GET")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Profile $profile)
    {
        $isAnon = $this->isAnon();
        $isPublished = $profile->getIsPublished();
        $isOwner = !$isAnon && $this->getUser()->isEqual($profile->getUser());
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isPublished && !$isOwner && !$isAdmin) {
            $this->addFlash(
                'profileIndex',
                ['warning' => $this->translator->trans("flash.profile.not.reachable",
                    ['%profilename%' => $profile->getFirstName() . ' ' . $profile->getLastName()], 'flashes')]
            );
            return $this->redirect($this->generateUrl('tj_profile_profile_index'));
        }

        $trackable = !$isAnon && !$this->getProfile()->getDoNotTrackViews();

        if (!$isOwner && !$trackable) {
            // Mark entity Seen
            $doNotTrack = $profile->getDoNotTrackViews();
            $this->viewEvent(Profile::class, $profile->getId(), $this->getUser(), $doNotTrack);
        }

        if (!$isAnon && !$profile->getContactSection()) {
            $profileContactSection = new ContactSection();
            $profileContactSection->setProfile($profile);
            $profile->setContactSection($profileContactSection);
            $this->em->persist($profile);
        }

        $allInone = $this->allInOne($profile, 6);
        $profilePhoto = $profile->getProfilePhoto();

        if ($profilePhoto) {
            $image = $profilePhoto;
            $editFormImage = $this->createNoStepEditFormMedia(MediaImageType::class, $image, 'tj_profile_media_image_edit');
        } else {
            $image = new MediaImage();
            $image->setProfile($profile);
            $image->setIsProfilePhoto(1);
            $editFormImage = $this->createNoStepNewFormMedia(MediaImageType::class, $image, 'tj_profile_media_image_new');
        }
        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);

        $seoDescription = $this->translator
            ->trans("seo.profile.description", ['%profilName%' => $profile->getFullName()], 'seo');

        $tileProfile = $profile->getSubtitle();

        $this->seo
            ->setTitle(sprintf('%s-%s-Theapolis', $tileProfile, $profile->getSubtitle2()))
            ->addMeta('name', 'description', $seoDescription)
            ->addMeta('name', 'keywords', $this->translator->trans("seo.profile.keywords", [], 'seo'));

        return $this->render('TheaterjobsProfileBundle:Profile:show.html.twig',
            [
                'entity' => $profile,
                'owner' => $isOwner,
                'edit_form_image' => $editFormImage->createView(),
                'participations' => $allInone['participations'],
                'yearsField' => $allInone['yearsInField'],
                'experiences' => $allInone['experiences']
            ]
        );
    }

    public static function allInOne(Profile $profile, $limit)
    {
        $participationsAll = $profile->getProductionParticipations();
        $participationsLimit = new ArrayCollection($participationsAll->slice(0, $limit));
        $experienceAll = $profile->getExperiences();
        $experienceLimit = new ArrayCollection($experienceAll->slice(0, $limit));
        $educationAll = $profile->getQualificationSection() ? $profile->getQualificationSection()->getQualifications() : [];

        $participationsLimitNew = [];
        $experienceLimitNew = [];
        $yearsInFieldArray = [];

        foreach ($participationsLimit as $participation) {
            if ($participation->getOngoing() == true) {
                $participationsLimitNew[10000][] = $participation;
            } else {
                $participationsLimitNew[$participation->getStart()->format('Y')][] = $participation;
            }
        }

        foreach ($participationsAll as $participation) {
            if ($participation->getOngoing() == true) {
                $date = new \DateTime();
                $yearsInFieldArray = array_merge(range($participation->getStart()->format('Y'), $date->format('Y')), $yearsInFieldArray);
            } else {
                $yearsInFieldArray = array_merge(range($participation->getStart()->format('Y'), $participation->getEnd()->format('Y')), $yearsInFieldArray);
            }
        }

        foreach ($experienceLimit as $experience) {
            if ($experience->getOngoing() == true) {
                $experienceLimitNew[10000][] = $experience;
            } else {
                $experienceLimitNew[$experience->getStart()->format('Y')][] = $experience;
            }
        }
        krsort($experienceLimitNew);

        foreach ($experienceAll as $experience) {
            if ($experience->getOngoing() == true) {
                $date = new \DateTime();
                $yearsInFieldArray = array_merge(range($experience->getStart()->format('Y'), $date->format('Y')), $yearsInFieldArray);
            } else {
                $yearsInFieldArray = array_merge(range($experience->getStart()->format('Y'), $experience->getEnd()->format('Y')), $yearsInFieldArray);
            }
        }

        foreach ($educationAll as $education) {
            if ($education->getStartDate()) {
                if (!$education->getFinished()) {
                    $date = new \DateTime();
                    $yearsInFieldArray = array_merge(range($education->getStartDate(), $date->format('Y')), $yearsInFieldArray);
                } else {
                    $yearsInFieldArray = array_merge(range($education->getStartDate(), $education->getEndDate()), $yearsInFieldArray);
                }
            }
        }
        krsort($participationsLimitNew);
        return ['participations' => $participationsLimitNew, 'yearsInField' => count(array_unique($yearsInFieldArray)), 'experiences' => $experienceLimitNew];
    }

    /**
     * Creates a form to delete a Profile entity.
     *
     * @param $id
     * @param $mediaType
     * @param Profile $profile
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteFormMedia($id, $mediaType, Profile $profile = null)
    {
        if (!$id) {
            throw $this->createNotFoundException('Unable to find Media entity.');
        }

        return $this->createFormBuilder()->setAction(
            $this->generateUrl('tj_profile_media_delete',
                [
                    'mediaType' => $mediaType,
                    'id' => $id, 'slug' => $profile
                ]
            )
        )
            ->setMethod('DELETE')
            ->add('submit', 'submit',
                [
                    'label' => $this->translator->trans('button.delete', [], 'forms'),
                    'attr' => ['class' => 'btn-inverse']
                ])
            ->getForm();
    }

    /**
     * Add job to user favourites list
     *
     * @param Profile $profile2
     * @internal param $Profile
     * @Route("/add-profile-favourite/{slug}", name="tj_profile_favourite_root", options={"expose"=true, "i18n" = false})
     * @Method("GET")
     * @return JsonResponse
     */
    public function addProfileToFavouritesAction(Profile $profile2)
    {
        $profile1 = $this->getUser()->getProfile();
        $status['status'] = 'ERROR';

        if (!$profile1->getUserFavourite()->contains($profile2)) {
            $profile1->addUserFavourite($profile2);
            $this->em->persist($profile1);
            $this->em->flush();
            $status['status'] = "SUCCESS";
        }

        return new JsonResponse($status);
    }

    /**
     * @Route( "/remove-profile-favourite/{slug}", name="tj_profile_remove_favourite_root", options={"expose"=true, "i18n" = false})
     * @Method("GET")
     * @param Profile $profile2
     * @return JsonResponse
     */
    public function removeProfileFromFavouritesAction(Profile $profile2)
    {
        $profile1 = $this->getUser()->getProfile();
        $status = ['status' => 'ERROR'];

        if ($profile1->getUserFavourite()->contains($profile2)) {
            $profile1->removeUserFavourite($profile2);
            $this->em->persist($profile1);
            $this->em->flush();
            $status['status'] = "SUCCESS";
        }

        return new JsonResponse($status);
    }

    /**
     * @TODO THIS HAS TO BE MOVED TO A DEDICATED CONTROLLER!!!
     * *waitForFiles
     * @param array $arrCat
     * @Route("/getCategoryType/{categoryId}" , name="tj_profile_profile_getCategoryType", options={"expose"=true, "i18n" = false})
     * @Method({"GET"})
     * @return JsonResponse
     */
    public function getCategoryTypeAction($arrCat)
    {
        $array = [];

        foreach ($arrCat as $categoryId) {
            $category = $this->em->getRepository(
                'TheaterjobsCategoryBundle:Category'
            )->find($categoryId);

            $types = $this->em->getRepository(
                'TheaterjobsProfileBundle:TypeOfCategory'
            )->findByCategory($category);
            foreach ($types as $type) {
                $array['type'][] = $type->getName();
            }
        }
        return new JsonResponse($array);
    }

    /**
     * Checks if user is able to update profile
     * Generate frontend render template
     *
     * @param Profile $profile
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function publishProfile(Profile $profile)
    {
        $errors = [];

        $checkPublishArray = $this->checkCanBePublish($profile);

        if (!$checkPublishArray['hasProfilePhoto']) {
            $errors[] = $this->translator->trans("publish.profile.required.ProfilePhoto", [], 'flashes');
        }
        if (!$checkPublishArray['hasProfileUnderTitle']) {
            $errors[] = $this->translator->trans("publish.profile.required.Undertitle", [], 'flashes');
        }
        if (!$checkPublishArray['hasProfileContactSection']) {
            $errors[] = $this->translator->trans("publish.profile.required.contactSection", [], 'flashes');
        }
        if (!$checkPublishArray['hasProfileEducation'] && !$checkPublishArray['hasProfileProductionParticipators'] && !$checkPublishArray['hasProfileExperiences']) {
            $errors[] = $this->translator->trans("publish.profile.required.Education", [], 'flashes');
            $errors[] = $this->translator->trans("publish.profile.required.productionExperience", [], 'flashes');
        }

        if ($errors) {
            return [
                'error' => true,
                'text' => $this->render('TheaterjobsProfileBundle:Partial\publishValidation.html.twig',
                    ['errors' => $errors])->getContent()
            ];
        }
        //Update Profile
        $profile->setIsPublished(true);
        $this->em->flush();
        return [
            'publish' => true,
            'text' => $text = $this->translator->trans("profile.success.published", [], 'flashes')
        ];
    }


    /**
     *
     * @Route("/change-status/{slug}", name="tj_profile_user_publish", options={"expose"=true, "i18n" = false})
     * @Method("PUT")
     * @param Request $request
     * @param Profile $profile
     * @return string|JsonResponse
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function profilePublishAction(Request $request, Profile $profile)
    {
        $status = $request->request->getInt('status');
        if ($status == 0) {
            $profile->setIsPublished(false);
            $profile->setUnPublishedAt(Carbon::now());
            $this->em->flush();

            $text = $this->translator->trans("profile.success.unpublished", [], 'flashes');
            return new JsonResponse([
                'unpublish' => true,
                'text' => $text
            ]);

        } elseif ($status == 1) {
            if (!$this->isGranted('ROLE_MEMBER')) {
                $errors = [$this->translator->trans('publish.profile.notAllowed')];
                return new JsonResponse([
                    'error' => true,
                    'text' => $this->render('TheaterjobsProfileBundle:Partial\publishValidationBecomeMember.html.twig',
                        ['errors' => $errors])->getContent()
                ]);
            }
            $response = $this->publishProfile($profile);
            return new JsonResponse($response);
        }
        return new JsonResponse(['error' => true, 'text' => 'Invalid value']);
    }


    /**
     *
     * @Route("/actuality/{slug}", name="tj_profile_user_actuality", options={"i18n" = false})
     * @Method({"POST", "PUT"})
     *
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function actualityAction(Request $request, Profile $profile)
    {
        $isOwner = !$this->isAnon() && $this->getUser()->isEqual($profile->getUser());
        $form = $this->createCreateForm(ActualityType::class, $profile, [], 'tj_profile_user_actuality', ['slug' => $profile->getSlug()]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $profile->setProfileActualityDate(Carbon::now());
            $this->readNotification($profile, 'profile_old_actuality', $profile->getUser());

            $content = $this->render('TheaterjobsProfileBundle:Partial:profileActuality.html.twig', [
                'entity' => $profile,
                'owner' => $isOwner
            ]);
            return new JsonResponse([
                'success' => true,
                'data' => $content->getContent()
            ]);
        }
        return new JsonResponse([
            'success' => false,
            'errors' => $this->getErrorMessagesAJAX($form)
        ]);
    }

    /**
     * Lists all recent activity.
     *
     * @Route("/modal/production/{slug}", name="profile_production_views", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @param Profile $profile
     * @return Response
     */
    public
    function showProductionAction(Profile $profile)
    {
        $participations = [];
        $partic = $profile->getProductionParticipations();

        foreach ($partic as $participation) {
            if ($participation->getOngoing() == true) {
                $participations[10000][] = $participation;
            } else {
                $participations[$participation->getStart()->format('Y')][] = $participation;
            }
        }

        if ($partic) {
            krsort($participations);
        }
        $isOwner = !$this->isAnon() && $this->getUser()->isEqual($profile->getUser());

        return $this->render('TheaterjobsProfileBundle:Modal:productionAll.html.twig', [
                'entity' => $profile,
                'participations' => $participations,
                'owner' => $isOwner
            ]
        );
    }

    /**
     * Lists all recent activity.
     *
     * @Route("/modal/experience/{slug}", name="profile_experience_views", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @param Profile $profile
     * @return Response
     */
    public function showExperienceAction(Profile $profile)
    {
        $experiences = [];
        $exper = $profile->getExperiences();
        $isAnon = $this->isAnon();
        $isOwner = !$isAnon && $this->getUser()->isEqual($profile->getUser());

        foreach ($exper as $experience) {
            if ($experience->getOngoing() == true) {
                $experiences[10000][] = $experience;
            } else {
                $experiences[$experience->getStart()->format('Y')][] = $experience;
            }
        }

        if ($exper) {
            krsort($experiences);
        }


        return $this->render('TheaterjobsProfileBundle:Modal:experienceAll.html.twig', [
                'entity' => $profile,
                'experiences' => $experiences,
                'owner' => $isOwner
            ]
        );
    }

    /**
     * Lists all recent activity.
     *
     * @Route("/modal/education", name="profile_education_views", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @return mixed
     */
    public function showEducationAction()
    {
        return $this->render('TheaterjobsProfileBundle:Modal:educationAll.html.twig', [
                'entity' => $this->getUser()->getProfile()
            ]
        );
    }

    /**
     * Lists all recent activity.
     *
     * @Route("/modal/old/{param}/{slug}", name="profile_show_old", options={"i18n" = false})
     * @param $param
     * @param Profile $profile
     * @return Response
     */
    public function showOldBoxesAction($param, Profile $profile)
    {
        $isAnon = $this->isAnon();
        $isOwner = !$isAnon && $this->getUser()->isEqual($profile->getUser());
        $deleteFormView = null;

        switch ($param) {
            case 'experience':
                $identifier = $profile->getOldExperience()->getId();
                $content = $profile->getOldExperience()->getExperience();
                $paramHeader = $this->translator->trans('people.show.modal.maintitle.oldExperience', [], 'messages');
                break;
            case 'education':
                $identifier = $profile->getOldEducation()->getId();
                $content = $profile->getOldEducation()->getEducation();
                $paramHeader = $this->translator->trans('people.show.modal.maintitle.oldEducation', [], 'messages');
                break;
            case 'extra':
                $identifier = $profile->getOldExtras()->getId();
                $content = $profile->getOldExtras()->getExtras();
                $paramHeader = $this->translator->trans('people.show.modal.maintitle.oldExtra', [], 'messages');
                break;
            default:
                throw new BadRequestHttpException('Unsupported box!');
        }


        if ($isOwner) {
            $deleteForm = $this->createDeleteFormOldBoxes($identifier, $param, $this->getProfile());
            $deleteFormView = $deleteForm->createView();
        }

        return $this->render('TheaterjobsProfileBundle:Modal:oldBoxesAll.html.twig', [
                'content' => $content,
                'paramHeader' => $paramHeader,
                'param' => $param,
                'delete_form' => $deleteFormView,
                'owner' => $isOwner
            ]
        );
    }

    /**
     *
     * @Route("/edit/general/{slug}", name="tj_profile_general_data", defaults={"slug" = null}, condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function editGeneralDataAction(Request $request, Profile $profile = null)
    {
        if (!$profile) {
            $profile = $this->getProfile();
        }
        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);
        $limit = !$this->isGranted('ROLE_MEMBER');

        $options = [
            'drive_licence_choice_list' => $this->choiceListFactory->getChoiceList($this->drivelicenceRoot),
            'voice_category_choice_list' => $this->choiceListFactory->getChoiceList($this->voicecategoryRoot),
        ];

        $editForm = $this->createEditForm(ProfileDataType::class, $profile, $options, 'tj_profile_general_data', ['slug' => $profile->getSlug()]);
        $isOwner = $this->getUser()->isEqual($profile->getUser());

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->updateProfile($profile);
            return $this->render('TheaterjobsProfileBundle:Partial:profileSectionPartial.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ]
            );
        } else if ($editForm->isSubmitted()) {
            return new JsonResponse(['errors' => $this->getErrorMessages($editForm)]);
        }

        return $this->render('TheaterjobsProfileBundle:Modal:general.html.twig', [
                'entity' => $profile,
                'edit_form' => $editForm->createView(),
                'limit' => $limit
            ]
        );
    }

    /**
     *
     * @Route("/edit/geo/{slug}", name="tj_profile_geo", defaults={"slug" = null}, options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function editGeoAction(Request $request, Profile $profile = null)
    {
        if (!$profile) {
            $profile = $this->getProfile();
        }

        $oldProfile = clone $this->em->getRepository(Profile::class)->findOneById($profile->getId());
        $sections = [];

        if (!$profile->getContactSection()) {
            $profileContactSection = new ContactSection();
            $profileContactSection->setProfile($profile);
            $profile->setContactSection($profileContactSection);
            $this->em->persist($profile);
            $this->em->flush();
        } else {
            $profileContactSection = $profile->getContactSection();
        }
        $formName = GeoLocationSectionType::class;
        $params = ['slug' => $profile->getSlug()];
        $editForm = $this->createEditForm($formName, $profileContactSection, [], 'tj_profile_geo', $params);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            //Update profile
            $this->updateProfile($profile, false);
            // Find changes
            $log = $this->findPropertyChanges($profile, $oldProfile, $sections, null, null, null);
            // Create User Activity Log
            $this->createUserActivityEvent($profile, $this->translator->trans('tj.user.activity.profile.update', [], 'activity'), $log, $profile->getUser());
            $isOwner = $this->getUser()->isEqual($profile->getUser());
            // Handle XHR here
            return $this->render('TheaterjobsProfileBundle:Partial:profileLocation.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ]
            );
        }
        return new JsonResponse($this->getErrorMessagesAJAX($editForm));
    }

    /**
     * Open modal that shows profile location on map
     *
     * @Route("/show/location/{slug}", name="tj_profile_show_location", defaults={"slug" = null}, options={"i18n" = false})
     * @param Profile $profile
     * @return Response
     */
    public function showLocationProfile(Profile $profile)
    {
        return $this->render('TheaterjobsProfileBundle:Modal:mapShow.html.twig', [
            'entity' => $profile,
        ]);
    }


    /**
     *
     * @Route(
     *     "/edit/contact/{slug}",
     *     name="tj_profile_contact",
     *     defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"i18n" = false}
     * )
     *
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|JsonResponse
     * @Security("is_granted('ROLE_USER')")
     */
    public function editContactAction(Request $request, Profile $profile)
    {
        $isPublished = $profile->getIsPublished();

        $oldProfile = clone $this->em->getRepository('TheaterjobsProfileBundle:Profile')->findOneById($profile->getId());

        $profileContactSection = $profile->getContactSection();

        if (!$profileContactSection) {
            $profileContactSection = new ContactSection();
            $profileContactSection->setProfile($profile);
            $profile->setContactSection($profileContactSection);
            $this->em->persist($profile);
            $this->em->flush();
        }

        $formName = ContactSectionType::class;
        $params = ['slug' => $profile->getSlug()];
        $editForm = $this->createEditForm($formName, $profileContactSection, [], 'tj_profile_contact', $params);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {

            //If contact section update is empty and profile is published
            if (empty($editForm["contact"]->getData()) && $isPublished) {
                $result = [
                    'success' => false,
                    'messages' => [
                        $this->translator->trans('profile.unpublished.flashes.contact.notEmpty', [], 'flashes')
                    ]
                ];
                return new JsonResponse($result);
            }

            //Update profile
            $this->updateProfile($profile, false);

            // Find changes
            $log = $this->findPropertyChanges($profile, $oldProfile, [], null, null, null);

            // Create User Activity Log
            $this->createUserActivityEvent($profile, $this->translator->trans('tj.user.activity.profile.update', [], 'activity'), $log, $profile->getUser());
            $response = null;

            $isOwner = $this->getUser()->isEqual($profile->getUser());
            $response = $this->render('TheaterjobsProfileBundle:Partial:socialsPartial.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ]
            )->getContent();

            return new JsonResponse([
                'success' => true,
                'data' => $response
            ]);

        }
        return new JsonResponse([
            'success' => false,
            'messages' => $this->getErrorMessagesAJAX($editForm)
        ]);
    }

    /**
     *
     * @Route("/edit/biography/{slug}", name="tj_profile_biography", defaults={"slug" = null}, options={"i18n" = false})
     * @Route("/edit/biography/{slug}", name="tj_profile_modal_biography", defaults={"slug" = null}, condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function editBiographyAction(Request $request, Profile $profile = null)
    {
        if (!$profile) {
            $profile = $this->getProfile();
        }

        $oldProfile = clone $this->em->getRepository('TheaterjobsProfileBundle:Profile')->findOneById($profile->getId());
        $sections = ['getBiographySection' => $this->getSectionValues($oldProfile->getBiographySection())];
        $limit = !$this->isGranted('ROLE_MEMBER');

        $biographySection = $profile->getBiographySection();
        if (!$biographySection) {
            $biographySection = new BiographySection();
            $biographySection->setProfile($profile);
            $profile->setBiographySection($biographySection);
            $this->em->persist($profile);
            $this->em->flush();
        }
        $opts = ['attr' => ["isEdit" => true]];
        $editForm = $this->createEditForm(BiographySectionType::class, $biographySection, $opts, 'tj_profile_biography', ['slug' => $profile->getSlug()]);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            //Update profile
            $this->updateProfile($profile, false);

            $isOwner = $this->getUser()->isEqual($profile->getUser());
            // Find changes
            $log = $this->findPropertyChanges($profile, $oldProfile, $sections, null, null, null);

            // Create User Activity Log
            $this->createUserActivityEvent($profile, $this->translator->trans('tj.user.activity.profile.update', [], 'activity'), $log, $profile->getUser());
            // Handle XHR here
            return $this->render('TheaterjobsProfileBundle:Partial:profileBio.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ]
            );
        }

        return $this->render('TheaterjobsProfileBundle:Modal:bio.html.twig', [
                'entity' => $profile,
                'edit_form' => $editForm->createView(),
                'limit' => $limit
            ]
        );
    }

    /**
     * Change profile picture with current profile pictures
     * @Route("/profile-photo", name="tj_profile_media_image_profile", options={"i18n" = false})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function mediaImageProfileAction(Request $request)
    {
        $id = $request->query->getInt('id');
        $profileImageId = null;
        $profileImage = $this->getProfile()->getProfilePhoto();

        if ($profileImage) {
            $profileImageId = $profileImage->getId();
        }
        try {
            $response = 'The dropped image is currently a profile photo!';
            if ($profileImageId !== $id) {
                $newPrifileImage = $this->em->getRepository('TheaterjobsProfileBundle:MediaImage')->findOneById($id);
                $newPrifileImage->setisProfilePhoto(true);
                if ($profileImageId !== null) {
                    $profileImage->setisProfilePhoto(false);
                }
                $this->em->flush();
                $response = 'The profile image was updated successfully!';
            }
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($response);
    }

    /**
     *
     * @Route("/edit/image/{id}", name="tj_profile_media_image_edit", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param MediaImage $mediaImage
     * @return Response
     */
    public function editMediaImageAction(Request $request, MediaImage $mediaImage)
    {
        $profile = $this->getProfile();
        $limit = !$this->isGranted('ROLE_MEMBER');

        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);
        $editForm = $this->createNoStepEditFormMedia(MediaImageType::class, $mediaImage, 'tj_profile_media_image_edit');

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $profile->addMediaImage($mediaImage);
                $this->updateProfile($profile);

                // for profile photo no need to return the slider
                if ($mediaImage->getIsProfilePhoto()) {
                    return new JsonResponse(['success' => true]);
                }

                return $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                    'entity' => $profile,
                    'owner' => true
                ]);
            } else {
                return $this->getErrorMessagesAJAX($editForm);
            }
        }

        $deleteForm = $this->createDeleteFormMedia($mediaImage->getId(), 'image');
        return $this->render('TheaterjobsProfileBundle:Modal/edit:mediaImage.html.twig', [
            'entity' => $mediaImage,
            'edit_form' => $editForm->createView(),
            'limit' => $limit,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     *
     * @Route("/new/image", name="tj_profile_media_image_new", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newMediaImageAction(Request $request)
    {
        $profile = $this->getProfile();
        $limit = !$this->isGranted('ROLE_MEMBER');
        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);

        $imageCount = $profile->getMediaImage()->count();
        if ($imageCount >= $this->container->getParameter('img_limit')) {
            return new JsonResponse([
                'error' => true,
                'errorMsg' => $this->translator->trans("flash.profile.media.image.limit.reached", [], 'flashes')
            ]);
        }

        $mediaImage = new MediaImage();
        $newForm = $this->createNoStepNewFormMedia(MediaImageType::class, $mediaImage, 'tj_profile_media_image_new');
        $newForm->handleRequest($request);

        if ($newForm->isSubmitted()) {
            if ($newForm->isValid()) {
                $profile->addMediaImage($mediaImage);
                $this->updateProfile($profile);
                // for profile photo no need to return the slider
                if ($mediaImage->getIsProfilePhoto()) {
                    return new JsonResponse(['success' => true]);
                }

                return new JsonResponse([
                        'error' => false,
                        'data' => $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                            'entity' => $profile,
                            'owner' => true
                        ])->getContent()]
                );
            }
            return new JsonResponse([
                'error' => true,
                'errors' => $this->getErrorMessagesAJAX($newForm)
            ]);
        }

        return $this->render('TheaterjobsProfileBundle:Modal/new:mediaImage.html.twig', [
                'entity' => $mediaImage,
                'edit_form' => $newForm->createView(),
                'limit' => $limit
            ]
        );
    }


    /**
     *
     * @Route("/edit/audio/{id}", name="tj_profile_media_audio_edit", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param MediaAudio $mediaAudio
     * @return Response
     */
    public function editMediaAudioAction(Request $request, MediaAudio $mediaAudio)
    {
        $profile = $this->getProfile();
        $limit = !$this->isGranted('ROLE_MEMBER');
        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);

        $editForm = $this->createNoStepEditFormMedia(MediaAudioType::class, $mediaAudio, 'tj_profile_media_audio_edit');

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $profile->addMediaAudio($mediaAudio);
                //Update profile
                $this->updateProfile($profile);
                return new JsonResponse([
                    'error' => false,
                    'data' => $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                        'entity' => $profile,
                        'owner' => true
                    ])->getContent()
                ]);
            }
            return new JsonResponse([
                'error' => true,
                'data' => $this->getErrorMessagesAJAX($editForm)
            ]);
        }
        $deleteForm = $this->createDeleteFormMedia($mediaAudio->getId(), 'audio');
        return $this->render('TheaterjobsProfileBundle:Modal/edit:mediaAudio.html.twig', [
            'entity' => $mediaAudio,
            'edit_form' => $editForm->createView(),
            'limit' => $limit,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     *
     * @Route("/new/audio", name="tj_profile_media_audio_new", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function newMediaAudioAction(Request $request)
    {
        $profile = $this->getProfile();
        $limit = !$this->isGranted('ROLE_MEMBER');
        $profile->setLimit($this->container->getParameter('disk_quota') * 1024 * 1024);

        if ($profile->getMediaAudio()->count() >= $this->container->getParameter('audio_limit')) {
            $msgError = $this->translator->trans("flash.profile.media.audio.limit.reached", [], 'flashes');
            return new JsonResponse([
                'error' => true,
                'errorMsg' => $msgError
            ]);
        }
        $mediaAudio = new MediaAudio();
        $editForm = $this->createNoStepNewFormMedia(MediaAudioType::class, $mediaAudio, 'tj_profile_media_audio_new');

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $profile->addMediaAudio($mediaAudio);
                //Update profile
                $this->updateProfile($profile);
                return new JsonResponse([
                    'error' => false,
                    'data' => $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                        'entity' => $profile,
                        'owner' => true
                    ])->getContent()
                ]);
            }
            return new JsonResponse([
                'error' => true,
                'errors' => $this->getErrorMessagesAJAX($editForm)
            ]);
        }

        return $this->render('TheaterjobsProfileBundle:Modal/new:mediaAudio.html.twig', [
            'entity' => $mediaAudio,
            'edit_form' => $editForm->createView(),
            'limit' => $limit
        ]);
    }

    /**
     *
     * @Route("/new/video", name="tj_profile_media_video_new", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Route("/edit/video/{id}", name="tj_profile_media_video_edit", condition="request.isXmlHttpRequest()", options={"i18n" = false})
     * @Method({"GET", "PUT","POST"})
     * @param Request $request
     * @param EmbededVideos|null $embededVideo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function mediaVideoAction(Request $request, EmbededVideos $embededVideo = null)
    {
        $profile = $this->getProfile();
        $limit = !$this->isGranted('ROLE_MEMBER');

        if (!$embededVideo) {
            $embededVideo = new EmbededVideos();
            $editForm = $this->createNoStepNewFormMedia(VideoType::class, $embededVideo, 'tj_profile_media_video_new');
        } else {
            $editForm = $this->createNoStepEditFormMedia(VideoType::class, $embededVideo, 'tj_profile_media_video_edit');
        }

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $isOwner = $this->getUser()->isEqual($profile->getUser());
            // a small trick for sorting (since the updated field is manged in the db level)
            $embededVideo->setUpdatedAt(Carbon::now());
            // add child directly into the parent ( can be done vise versa but sometimes it has a delay)
            // and its better less operation to make
            $profile->addVideo($embededVideo);
            //Update profile
            $this->updateProfile($profile, false);
            $this->em->flush();
            return $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ]
            );
        }

        if ($embededVideo->getId()) {
            $deleteForm = $this->createDeleteFormMedia($embededVideo->getId(), 'video');
            return $this->render('TheaterjobsProfileBundle:Modal/edit:mediaVideo.html.twig', [
                    'entity' => $embededVideo,
                    'edit_form' => $editForm->createView(),
                    'limit' => $limit,
                    'delete_form' => $deleteForm->createView(),
                ]
            );

        } else {
            return $this->render('TheaterjobsProfileBundle:Modal/new:mediaVideo.html.twig', [
                    'entity' => $embededVideo,
                    'edit_form' => $editForm->createView(),
                    'limit' => $limit
                ]
            );
        }
    }

    /**
     * @Route(
     *     "/new/qualification/{slug}",
     *      name="tj_profile_qualification_new",
     *      defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Profile $profile
     * @return Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function qualificationAddAction(Request $request, Profile $profile = null)
    {
        $limit = true;
        if (!$profile) {
            $profile = $this->getProfile();
        }
        $options = [
            'profile_category_choice_list' => $this->choiceListFactory->getChoiceList($this->profilecategoryRoot),
        ];

        $qualificationSection = $profile->getQualificationSection();
        if ($qualificationSection === null) {
            $qualificationSection = new QualificationSection();
            $qualificationSection->setProfile($profile);
        }
        $formName = 'tj_profile_qualifications';
        $routeName = 'tj_profile_qualification_new';
        $routeOpts = ['slug' => $profile->getSlug()];

        $options['profile'] = $profile;
        $qualification = new Qualification();
        $newForm = $this->createCreateForm($formName, $qualification, $options, $routeName, $routeOpts);

        $newForm->handleRequest($request);
        if ($newForm->isValid()) {
            $qualificationSection->addQualification($qualification);
            $isOwner = $this->getUser()->isEqual($profile->getUser());
            $this->em->persist($qualificationSection);
            $profile->setQualificationSection($qualificationSection);
            $this->removeOldCategory($profile, $qualification);
            //Update profile
            $this->updateProfile($profile);
            $allInone = ProfileController::allInOne($profile, 6);
            return new JsonResponse([
                'education' => $this->render('TheaterjobsProfileBundle:Partial:educationPartial.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ])->getContent(),
                'boxes' => $this->render('TheaterjobsProfileBundle:Partial:profileBoxes.html.twig', [
                    'yearsField' => $allInone['yearsInField'],
                    'entity' => $profile,
                    'owner' => $isOwner
                ])->getContent()
            ]);
        }
        if ($newForm->isSubmitted() && !$newForm->isValid()) {
            return new JsonResponse([
                'errors' => $this->getErrorMessagesAJAX($newForm)
            ]);
        }
        return $this->render('TheaterjobsProfileBundle:Modal/new:education.html.twig', [
                'edit_form' => $newForm->createView(),
                'limit' => $limit
            ]
        );
    }


    /**
     * @Route(
     *     "/edit/qualification/{id}/{slug}",
     *      name="tj_profile_qualification_edit",
     *     defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"i18n" = false})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param null $id
     * @param Profile $profile
     * @return Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function qualificationEditAction(Request $request, $id = null, Profile $profile = null)
    {
        $limit = true;
        if (!$profile) {
            $profile = $this->getProfile();
        }
        $options = [
            'profile_category_choice_list' => $this->choiceListFactory->getChoiceList($this->profilecategoryRoot),
        ];

        $qualificationSection = $profile->getQualificationSection();
        $routeNameEdit = 'tj_profile_qualification_edit';
        $routeOpts = ['id' => $id, 'slug' => $profile->getSlug()];
        $formName = 'tj_profile_qualifications';

        $qualification = $this->em->getRepository(Qualification::class)->find($id);
        $editForm = $this->createEditForm($formName, $qualification, $options, $routeNameEdit, $routeOpts);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $isOwner = $this->getUser()->isEqual($profile->getUser());
            $this->em->persist($qualificationSection);
            $profile->setQualificationSection($qualificationSection);
            $this->removeOldCategory($profile, $qualification);
            //Update profile
            $this->updateProfile($profile);
            $allInone = ProfileController::allInOne($profile, 6);
            return new JsonResponse([
                'education' => $this->render('TheaterjobsProfileBundle:Partial:educationPartial.html.twig', [
                    'entity' => $profile,
                    'owner' => $isOwner
                ])->getContent(),
                'boxes' => $this->render('TheaterjobsProfileBundle:Partial:profileBoxes.html.twig', [
                    'yearsField' => $allInone['yearsInField'],
                    'entity' => $profile,
                    'owner' => $isOwner
                ])->getContent()
            ]);
        }
        if ($editForm->isSubmitted() && !$editForm->isValid()) {
            return new JsonResponse([
                'errors' => $this->getErrorMessagesAJAX($editForm)
            ]);
        }
        $deleteForm = $this->createDeleteFormMedia($qualification->getId(), 'qualification');
        return $this->render('TheaterjobsProfileBundle:Modal/edit:education.html.twig', [
                'entity' => $qualification,
                'edit_form' => $editForm->createView(),
                'limit' => $limit,
                'delete_form' => $deleteForm->createView(),
            ]
        );
    }

    private function removeOldCategory(Profile $profile, Qualification $qualification)
    {
        if ($qualification->getCategories() && count($profile->getOldCategories()) > 0) {
            foreach ($profile->getOldCategories() as $categ) {
                $profile->removeOldCategory($categ);
            }
            $this->em->flush();
        }
        return true;
    }

    /**
     *
     * @Route("/delete/{mediaType}/{id}/{slug}", name="tj_profile_media_delete", defaults={"slug" = null}, options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"DELETE"})
     * @param Request $request
     * @param $id
     * @param $mediaType
     * @param Profile $profile
     * @return mixed
     */
    public function deleteMediaAction(Request $request, $id, $mediaType, Profile $profile = null)
    {
        if (!$profile) {
            $profile = $this->getProfile();
        }

        $isOwner = $this->getUser()->isEqual($profile->getUser());

        if ($mediaType == 'image') {
            $repository = 'TheaterjobsProfileBundle:MediaImage';
        } elseif ($mediaType == 'pdf') {
            $repository = 'TheaterjobsProfileBundle:MediaPdf';
        } elseif ($mediaType == 'audio') {
            $repository = 'TheaterjobsProfileBundle:MediaAudio';
        } elseif ($mediaType == 'video') {
            $repository = 'TheaterjobsProfileBundle:EmbededVideos';
        }

        if ($mediaType == 'qualification') {
            $isAble = $this->isAbleToDeleteSection($profile);
            if ($isAble) {
                $media = $this->em->getRepository('TheaterjobsProfileBundle:Qualification')->findOneBy(array('id' => $id, 'qualificationSection' => $profile->getQualificationSection()));
                if ($media) {
                    $this->em->remove($media);
                    $this->em->flush();

                    $allInone = ProfileController::allInOne($profile, 6);
                    return new JsonResponse([
                        'success' => true,
                        'data' => [
                            'education' => $this->render('TheaterjobsProfileBundle:Partial:educationPartial.html.twig', [
                                'entity' => $profile,
                                'owner' => $isOwner
                            ])->getContent(),
                            'boxes' => $this->render('TheaterjobsProfileBundle:Partial:profileBoxes.html.twig', [
                                'yearsField' => $allInone['yearsInField'],
                                'entity' => $profile,
                                'owner' => $isOwner
                            ])->getContent()
                        ]
                    ]);
                } else {
                    $result = [
                        'success' => false,
                        'messages' => array($this->translator->trans(
                            'profile.flash.error.not.found'
                        ))
                    ];
                    return new JsonResponse($result);
                }
            } else {
                $result = [
                    'success' => false,
                    'messages' => array($this->translator->trans(
                        'profile.flash.error.unpublished.first'
                    ))
                ];
                return new JsonResponse($result);
            }
        } else {
            $media = $this->em->getRepository($repository)->findOneBy(['id' => $id, 'profile' => $profile]);
        }

        if (!$media) {
            return new JsonResponse([
                'success' => false,
                'messages' => array($this->translator->trans('profile.flash.error.not.found'))
            ]);
        }

        $this->em->remove($media);

        //Update profile
        $this->updateProfile($profile);

        if ($request->isXmlHttpRequest()) {
            return $this->render('TheaterjobsProfileBundle:Partial:slider.html.twig', [
                'entity' => $profile,
                'owner' => $isOwner
            ]);
        } else {
            return $this->redirectToRoute('tj_profile_profile_show', ['slug' => $profile->getSlug()]);
        }
    }

    private function createNoStepNewFormMedia($formName, $entity, $route)
    {

        $form = $this->createForm($formName, $entity, [
            'action' => $this->generateUrl($route),
            'method' => 'POST',
            'attr' => [
                "isEdit" => false
            ]
        ]);

        $form->add('submit', 'submit', [
                'label' => $this->translator->trans('button.create', [], 'forms'),
                "attr" => ["class" => "btn btn-inverse-primary btn btn-inverse btn-primary"]
            ]
        );

        return $form;
    }

    private function createNoStepEditFormMedia($formType, $entity, $route)
    {

        $form = $this->createForm($formType, $entity, [
            'action' => $this->generateUrl($route, ['id' => $entity->getId()]),
            'method' => 'PUT',
            'attr' => [
                "isEdit" => true
            ]
        ]);

        $form->add('submit', 'submit', array('attr' => ['class' => 'btn btn-inverse-primary btn btn-inverse btn-primary'], 'label' => $this->translator->trans('button.update', [], 'forms')));

        return $form;
    }

    /**
     * Creates a form to delete a Profile entity.
     *
     * @param $id
     * @param $type
     * @param Profile $profile
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteFormOldBoxes($id, $type, Profile $profile = null)
    {
        if (!$id) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }
        return $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'tj_profile_delete_old', ['param' => $type, 'id' => $id, 'slug' => $profile->getSlug()]
                )
            )
            ->setMethod('GET')
            ->add('submit', 'submit', array('label' => $this->translator->trans('button.delete', array(), 'forms'),
                'attr' => array('class' => 'btn-inverse')))
            ->getForm();
    }

    /**
     *
     * @Route("/{param}/delete/{id}/{slug}", name="tj_profile_delete_old", defaults={"slug" = null}, options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Request $request
     * @param $id
     * @param $param
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteOldBoxesAction(Request $request, $id, $param, Profile $profile = null)
    {

        if (!$profile) {
            $profile = $this->getProfile();
        }

        if (!$profile) {
            throw $this->createNotFoundException('Unable to find Profile entity . ');
        }

        if ($param == 'experience') {
            $repository = 'TheaterjobsProfileBundle:OldExperience';
            $profile->setOldExperience(null);
        } elseif ($param == 'education') {
            $repository = 'TheaterjobsProfileBundle:OldEducation';
            $profile->setOldEducation(null);
        } elseif ($param == 'extra') {
            $repository = 'TheaterjobsProfileBundle:OldExtras';
            $profile->setOldExtras(null);
        }

        $oldBox = $this->em->getRepository($repository)->findBy(['id' => $id, 'profile' => $profile]);

        if ($oldBox) {
            $this->em->remove($oldBox[0]);
        }

        //Update profile
        $this->updateProfile($profile);

        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
            $response = new JsonResponse([
                'success' => true,
                'id' => $id
            ]);

        } else {
            $response = $this->redirectToRoute('tj_profile_profile_show', ['slug' => $profile->getSlug()]);
        }

        return $response;
    }

    /**
     *
     * @Route("/statistics/{slug}", name="tj_profile_statistics", options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method({"GET", "PUT"})
     * @param Profile $profile
     * @return response
     */
    public function showStatisticsAction(Profile $profile)
    {
        if ($profile === null) {
            $profile = $this->em->getRepository('TheaterjobsProfileBundle:Profile')
                ->findOneByUser($this->getUser());
        }

        if (!$profile) {
            throw $this->createNotFoundException('Unable to find Profile entity . ');
        }

        $lastTenDaysViews = $this->em->getRepository(View::class)->profileLastTenDaysViews(Profile::class, $profile->getId());
        $totalViews = $this->countAllViews(Profile::class, $profile->getId());

        // old views from migration
        $totalViewsOld = $profile->getTotalViews();

        $totalLast10Days = 0;
        foreach ($lastTenDaysViews as $viewCounter) {
            $totalLast10Days += $viewCounter['statCount'];
        }
        $trackViews = $profile->getDoNotTrackViews();

        return $this->render('TheaterjobsProfileBundle:Modal:statistics.html.twig', array(
                'profile' => $profile,
                'lastTenDaysViews' => $lastTenDaysViews,
                'totalViews' => $totalViews += $totalViewsOld,
                'totalLast10Days' => $totalLast10Days,
                'trackViews' => $trackViews
            )
        );

    }

    protected function getSectionValues($obj)
    {
        $methods = get_class_methods($obj);
        $result = array();
        if (count($methods) > 0) {
            foreach ($methods as $method) {
                $name = $this->cleanMethodName($method);

                if (substr($method, 0, 3) != 'get')
                    continue;

                if (is_object($obj->$method()) && !$obj->$method() instanceof \DateTime) {
                    continue;
                } elseif ($obj->$method() instanceof \DateTime) {
                    $result[$name] = $obj->$method()->format('d.m.Y');
                } elseif (is_array($obj->$method())) {
                    $result[$name] = implode(',', $obj->$method());
                } else {
                    $result[$name] = $obj->$method();
                }
            }
        }
        return $result;
    }

    // Find Changes of the Array Values

    private function cleanMethodName($method)
    {
        $name_chars = str_split(str_replace('get', '', $method));
        $modified_name_chars = array();
        foreach ($name_chars as $key => $char) {
            if (ctype_upper($char) && $key == 0) {
                $ch = strtolower($char);
            } elseif (ctype_upper($char) && $key != 0) {
                $ch = '_' . strtolower($char);
            } else {
                $ch = $char;
            }
            $modified_name_chars[] = $ch;
        }

        $name = implode('', $modified_name_chars);

        return $name;
    }

    /**
     * Finds the Diferences between 2 objects of type Inserate
     * @param $new
     * @param $old
     * @param $sections
     * @param $qualifications
     * @param $skills
     * @param $drive_license
     * @return string
     */
    protected function findPropertyChanges($new, $old, $sections, $qualifications, $skills, $drive_license)
    {

        // Get Methods of the Class
        $classMethods = get_class_methods($new);

        // Final Log Array
        $log = array();

        // Go throw all Entity Methods
        foreach ($classMethods as $key => $method) {
            // Exclude this Fields
            if (in_array($method, array('getMediaImage', 'getMediaAudio', 'getMediaPdf', 'getVideos', 'getProfileActualityDate', 'getShowWizard', 'getLimit', 'getUsedSpace', 'getLastBooking')))
                continue;


            // Exclude this Fields
            if (in_array($method, array('getUpdatedAt')))
                continue;

            // Get the Getters
            if (substr($method, 0, 3) == 'get') {
                // Prepare method name
                $name = $this->cleanMethodName($method);

                // If the field is not an object
                if (!is_object($new->$method()) && $new->$method() != $old->$method()) {
                    // If is submited as empty when edited from the Author.
                    $log[$name][0] = $old->$method() === null ? "" : $old->$method();
                    $log[$name][1] = $new->$method();

                    // If the Field is an Object
                } elseif (is_object($new->$method())) {
                    if (in_array($method, array_keys($sections))) {
                        if ($this->arrayChanges($this->getSectionValues($new->$method()), $sections[$method]))
                            $log[$name] = $this->arrayChanges($this->getSectionValues($new->$method()), $sections[$method]);
                    }

                    // Check the DateTime type Fields
                    if ($new->$method() instanceof \DateTime && $new->$method() != $old->$method()) {
                        $log[$name][0] = $old->$method() === null ? "" : $old->$method()->format('d.m.Y');
                        $log[$name][1] = $new->$method() === null ? "" : $new->$method()->format('d.m.Y');
                    }

                    // Check PersistentCollection Type Fields
                    if ($new->$method() instanceof PersistentCollection) {
                        if ($method == 'getProfessions') {
                            // getProfessions
                            $new_collection = array();
                            $old_collection = array();
                            $has_modified_collection = false;

                            // Get the old Categories
                            foreach ($old->$method()->getValues() as $co) {
                                $old_collection[] = $co->getName();
                            }

                            // Get the new Categories and check if the Categories have Changed
                            foreach ($new->$method()->getValues() as $cn) {
                                $new_collection[] = $cn->getName();
                                if (!in_array($cn->getName(), $old_collection))
                                    $has_modified_collection = true;
                            }

                            // If a category is been removed
                            if (count($old_collection) != count($new_collection)) {
                                $has_modified_collection = true;
                            }

                            if ($has_modified_collection) {
                                $log[$name][0] = $old_collection;
                                $log[$name][1] = $new_collection;
                            }
                        }
                    }

                    if (($method == 'getQualificationSection') && ($qualifications !== null)) {
                        // getUserFavourite
                        $new_collection = array();
                        $old_collection = array();
                        $has_modified_collection = false;

                        // Get the old Categories
                        foreach ($qualifications as $co) {
                            $old_collection[] = $co->getEducationType();
                        }

                        // Get the new Categories and check if the Categories have Changed
                        foreach ($new->$method()->getQualifications()->getValues() as $cn) {
                            $new_collection[] = $cn->getEducationType();
                            if (!in_array($cn->getEducationType(), $old_collection))
                                $has_modified_collection = true;
                        }

                        // If a category is been removed
                        if (count($old_collection) != count($new_collection)) {
                            $has_modified_collection = true;
                        }

                        if ($has_modified_collection) {
                            $log[$name][0] = implode(', ', $old_collection);
                            $log[$name][1] = implode(', ', $new_collection);
                        }
                    }

                    if (($method == 'getSkillSection') && ($skills !== null) && ($drive_license !== null)) {
                        // getUserFavourite
                        $new_collection_skills = array();
                        $new_collection_drive = array();
                        $old_collection_skills = array();
                        $old_collection_drive = array();
                        $has_modified_collection_skills = false;
                        $has_modified_collection_drive = false;

                        // Get the old Categories
                        foreach ($skills as $co) {
                            $old_collection_skills[] = $co->getSkill()->getTitle();
                        }

                        // Get the new Categories and check if the Categories have Changed
                        foreach ($new->$method()->getProfileSkill()->getValues() as $cn) {
                            $new_collection_skills[] = $cn->getSkill()->getTitle();
                            if (!in_array($cn->getSkill()->getTitle(), $old_collection_skills))
                                $has_modified_collection = true;
                        }

                        // If a category is been removed
                        if (count($old_collection_skills) != count($new_collection_skills)) {
                            $has_modified_collection_skills = true;
                        }

                        if ($has_modified_collection_skills) {
                            $log[$name . '_skills'][0] = implode(', ', $old_collection_skills);
                            $log[$name . '_skills'][1] = implode(', ', $new_collection_skills);
                        }

                        // Driving License

                        // Get the old Categories
                        foreach ($drive_license as $co) {
                            $old_collection_drive[] = $co->getTitle();
                        }

                        // Get the new Categories and check if the Categories have Changed
                        foreach ($new->$method()->getDriveLicense()->getValues() as $cn) {
                            $new_collection_drive[] = $cn->getTitle();
                            if (!in_array($cn->getTitle(), $old_collection_drive))
                                $has_modified_collection_drive = true;
                        }

                        // If a category is been removed
                        if (count($old_collection_drive) != count($new_collection_drive)) {
                            $has_modified_collection_drive = true;
                        }

                        if ($has_modified_collection_drive) {
                            $log[$name . '_drive'][0] = implode(', ', $old_collection_drive);
                            $log[$name . '_drive'][1] = implode(', ', $new_collection_drive);
                        }
                    }
                }
            }
        }
        // Unset the Last Values of the Foreach Loop
        unset($key);
        unset($method);

        $final_log = array();
        foreach ($log as $ke => $lo) {
            if (isset($lo[0])) {
                $final_log[$ke] = $lo;
            } else {
                foreach ($lo as $k => $l) {
                    if (isset($l[0]))
                        $final_log[$k] = $l;
                }
            }
        }

        if (count($final_log) === 0)
            return null;
        else
            return json_encode($final_log);
    }

    private function arrayChanges($new, $old)
    {
        $log = array();
        foreach ($new as $key => $value) {
            if (!is_object($value) && isset($old[$key])) {
                if ($old[$key] != $value) {
                    $log[$key] = array(
                        $old[$key],
                        $value
                    );
                }
            }
        }

        if (count($log) > 0)
            return $log;
    }

    /**
     * Create Event for Inserate Changes
     *
     * @param $entity
     * @param $message
     * @param null $log
     * @param null $user
     * @return bool
     */
    protected function createUserActivityEvent($entity, $message, $log = null, $user = null)
    {
        if ($log === null) {
            return false;
        }

        $activictyForAdmin = $entity->getUser() !== $this->getUser();
        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($entity, $message, $activictyForAdmin, $log, $user);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        return true;
    }

    /**
     *
     * @Route(
     *     "/edit/undertitle/{slug}",
     *     name="tj_profile_undertitle",
     *     defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"i18n" = false})
     * @ParamConverter("profile", options={"mapping": {"slug": "slug"}})
     * @Method("GET")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function editUndertitleAction(Profile $profile = null)
    {

        if (!$profile === null) {
            $profile = $this->getProfile();
        }
        $formName = ProfileSubtitleType::class;
        $routeOpt = ['slug' => $profile->getSlug()];
        $form = $this->createEditForm($formName, $profile, [], 'tj_profile_update_undertitle', $routeOpt);

        return $this->render('TheaterjobsProfileBundle:Modal:undertitle.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Updates a Profile entity.
     * @Route("/update/undertitle/{slug}", name="tj_profile_update_undertitle", options={"i18n" = false})
     * @Method("PUT")
     * @param Request $request
     * @param Profile $profile
     * @return JsonResponse
     * @Security("is_granted('ROLE_USER')")
     */
    public function updateUndertitleAction(Request $request, Profile $profile)
    {
        $isOwner = !$this->isAnon() && $this->getUser()->isEqual($profile->getUser());
        $formName = ProfileSubtitleType::class;
        $routeOpt = ['slug' => $profile->getSlug()];
        $form = $this->createEditForm($formName, $profile, [], 'tj_profile_update_undertitle', $routeOpt);

        $form->handleRequest($request);
        if ($form->isValid()) {

            if ($profile->getSubtitle() === null) {
                $profile->setProfileName(0);
            }

            //Update profile
            $this->updateProfile($profile);
            $data = $this->render('TheaterjobsProfileBundle:Partial:profileUndertitle.html.twig', [
                'entity' => $profile,
                'owner' => $isOwner,
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => $data->getContent()
            ]);
        }
        return new JsonResponse([
            'success' => false,
            'errors' => $this->getErrorMessagesAJAX($form)
        ]);
    }
}