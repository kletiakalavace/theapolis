<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\SocialMedia;
use Theaterjobs\AdminBundle\Entity\SocialMediaFile;
use Theaterjobs\AdminBundle\Form\SocialMediaFileType;
use Theaterjobs\AdminBundle\Form\SocialMediaSearchType;
use Theaterjobs\AdminBundle\Form\SocialMediaType;
use Theaterjobs\AdminBundle\Model\SocialMediaSearch;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * SocialMedia controller.
 *
 * @Route("/social-media")
 */
class SocialMediaController extends BaseController
{

    /**
     * Lists all SocialMedia entities.
     *
     * @Route("/index", name="admin_social_media")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $em = $this->getEM();

        // since we will have only one file we get always the first
        $socialMediaFile = $em->getRepository(SocialMediaFile::class)->getFirst();

        if ($socialMediaFile) {
            $form = $this->createEditForm(SocialMediaFileType::class,
                $socialMediaFile,
                [],
                'admin_social_media_file_edit',
                ['id' => $socialMediaFile->getId()]);
        } else {
            $socialMediaFile = new SocialMediaFile();
            $form = $this->createCreateForm(SocialMediaFileType::class,
                $socialMediaFile,
                [],
                'admin_social_media_file_new');
        }

        $socialMediaSearch = new SocialMediaSearch();
        $adminSocialMediaSearchForm = $this->createGeneralSearchForm(SocialMediaSearchType::class,
            $socialMediaSearch,
            [],
            'admin_load_social_media_index'
        );

        return $this->render('TheaterjobsAdminBundle:SocialMedia:index.html.twig', [
            'fileForm' => $form->createView(),
            'form' => $adminSocialMediaSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load_social_media", name="admin_load_social_media_index", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function loadSocialMedia(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $socialMediaSearch = new SocialMediaSearch();
        $adminSocialMediaSearchForm = $this->createGeneralSearchForm(SocialMediaSearchType::class,
            $socialMediaSearch,
            [],
            'admin_load_social_media_index'
        );

        $adminSocialMediaSearchForm->handleRequest($request);
        $adminSocialMediaSearch = $adminSocialMediaSearchForm->getData();

        $socialMedia = $em->getRepository(SocialMedia::class)->adminListSearch($adminSocialMediaSearch);

        $paginator = $this->getPaginator();

        $paginatedSocialMedia = $paginator->paginate($socialMedia, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedSocialMedia->getTotalItemCount();

        foreach ($paginatedSocialMedia as $socialMedia) {
            $updateUrl = $this->generateUrl('admin_social_media_edit', ['id' => $socialMedia->getId()]);
            $actionsColumn = "<a href='$updateUrl' data-target='#myModal' data-hash='edit' data-toggle='modal'
               data-color='#244372'>Edit</a>";
            $nameColumn = $socialMedia->getName();
            $positionColumn = $socialMedia->getPosition();
            $svgName = $socialMedia->getSvgName();
            $updatedAtColumn = ($socialMedia->getUpdatedAt()) ? $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $socialMedia->getUpdatedAt()])->getContent() : '';

            $records["data"][] = [
                $nameColumn,
                $svgName,
                $positionColumn,
                $updatedAtColumn,
                $actionsColumn
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Creates a new SocialMedia entity.
     *
     * @Route("/new", name="admin_social_media_new")
     * @Route("/edit/{id}", name="admin_social_media_edit")
     * @Method({"PUT", "POST"})
     * @param Request $request
     * @param SocialMedia|null $socialMedia
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createAction(Request $request, SocialMedia $socialMedia = null)
    {

        if ($socialMedia) {
            $form = $this->createEditForm(SocialMediaType::class,
                $socialMedia,
                [],
                'admin_social_media_update',
                ['id' => $socialMedia->getId()]);
        } else {
            $socialMedia = new SocialMedia();
            $form = $this->createCreateForm(SocialMediaType::class,
                $socialMedia,
                [],
                'admin_social_media_create');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $socialMediaFile = $this->getEM()->getRepository(SocialMediaFile::class)->getFirst();
            if ($socialMediaFile) {
                $socialMedia->setMediaFile($socialMediaFile);
                $this->getEM()->persist($socialMedia);
                $this->getEM()->flush();

                return new JsonResponse(['success' => true]);
            }
        }
        return new JsonResponse(['success' => false, 'errors' => $this->getErrorMessagesAJAX($form)]);
    }

    /**
     * Creates a new SocialMedia entity.
     *
     * @Route("/file/new", name="admin_social_media_file_new")
     * @Route("/file/edit/{id}", name="admin_social_media_file_edit")
     * @Method({"POST","PUT"})
     * @param Request $request
     * @param SocialMediaFile $socialMediaFile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function fileAction(Request $request, SocialMediaFile $socialMediaFile = null)
    {

        if ($socialMediaFile) {
            $form = $this->createEditForm(SocialMediaFileType::class,
                $socialMediaFile,
                [],
                'admin_social_media_file_edit',
                ['id' => $socialMediaFile->getId()]);
        } else {
            $socialMediaFile = new SocialMediaFile();
            $form = $this->createCreateForm(SocialMediaFileType::class,
                $socialMediaFile,
                [],
                'admin_social_media_file_new');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getEM()->persist($socialMediaFile);
            $this->getEM()->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'errors' => $this->getErrorMessagesAJAX($form)]);

    }

    /**
     * Displays a form to create a new SocialMedia entity.
     *
     * @Route("/new", name="admin_social_media_create")
     * @Route("/edit/{id}", name="admin_social_media_update")
     * @Method("GET")
     * @param SocialMedia $socialMedia
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function socialMediaAction(SocialMedia $socialMedia = null)
    {
        $deleteForm = null;
        if ($socialMedia) {
            $form = $this->createEditForm(SocialMediaType::class,
                $socialMedia,
                [],
                'admin_social_media_update',
                ['id' => $socialMedia->getId()]);
            $deleteForm = $this->createGeneralDeleteForm('admin_social_media_delete',
                ['id' => $socialMedia->getId()])->createView();
        } else {
            $socialMedia = new SocialMedia();
            $form = $this->createCreateForm(SocialMediaType::class,
                $socialMedia,
                [],
                'admin_social_media_create');
        }


        return $this->render('TheaterjobsAdminBundle:Modal:socialmedia.html.twig', [
            'entity' => $socialMedia,
            'form' => $form->createView(),
            'delete' => $deleteForm

        ]);
    }

    /**
     * Deletes a SocialMedia entity.
     *
     * @Route("/{id}", name="admin_social_media_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param SocialMedia $socialMedia
     * @return JsonResponse
     */
    public function deleteAction(Request $request, SocialMedia $socialMedia)
    {
        $form = $this->createGeneralDeleteForm('admin_social_media_delete',
            ['id' => $socialMedia->getId()]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (!$socialMedia) {
                throw $this->createNotFoundException('Unable to find SocialMedia entity.');
            }

            $this->getEM()->remove($socialMedia);
            $this->getEM()->flush();

            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false, 'errors' => $this->getErrorMessagesAJAX($form)]);

    }
}
