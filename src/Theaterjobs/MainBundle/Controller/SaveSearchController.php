<?php

namespace Theaterjobs\MainBundle\Controller;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\MainBundle\Entity\SaveSearch;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @Route("/saved-search", options={"expose"=true, "i18n" = false})
 * @Security("has_role('ROLE_USER')")
 */
class SaveSearchController extends BaseController
{

    /**
     * @var EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @var \Theaterjobs\MainBundle\Utility\SaveSearch
     * @DI\Inject("theaterjobs.main_bundle.save_search")
     */
    private $saveSearch;

    /**
     * @Route("/save", name="tj_main_save_search")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     * @Security("has_role('ROLE_MEMBER')")
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveSearchAction(Request $request)
    {
        $profile = $this->getProfile();
        $saveSearch = new SaveSearch();
        $opts = ['profile' => $this->getProfile()];
        $form = $this->createCreateForm('theaterjobs_main_saveSearch', $saveSearch, $opts, 'tj_main_save_search');
        if ($profile->getSearches()->count() > SaveSearch::LIMIT - 1) {
            $err = $this->getTranslator()->trans('save.search.limit.is %limit%', ['%limit%' => SaveSearch::LIMIT]);
            return new JsonResponse([
                'success' => false,
                'errors' => [$err]
            ]);
        }

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $saveSearch->setProfile($profile);
            $saveSearch->setParams($this->saveSearch->removeWhiteListed($saveSearch->getParams()));
            $this->em->persist($saveSearch);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $this->render('TheaterjobsMainBundle:SaveSearch:success.html.twig')->getContent()
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'errors' => $this->getErrorMessages($form)
        ]);
    }

    /**
     * @Route("/notification/{id}", name="tj_main_edit_notification_search")
     * @Method("POST")
     * @param Request $request
     * @param SaveSearch $saveSearch
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editNotificationAction(Request $request, SaveSearch $saveSearch)
    {
        $profile = $this->getUser()->getProfile();
        if ($profile->getId() == $saveSearch->getProfile()->getId()) {
            $notification = $request->request->get('notification');
            $saveSearch->setNotification($notification);
            $saveSearch->setUpdatedAt(Carbon::now());
            $this->em->persist($saveSearch);
            $this->em->flush();
            return new JsonResponse(['status' => $notification, 'hash' => $saveSearch->getHash()]);
        }
    }

    /**
     * @Route("/remove-search/{id}", name="tj_main_remove_search")
     * @Method("GET")
     * @param SaveSearch $saveSearch
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeSaveSearchAction(SaveSearch $saveSearch)
    {
        $profile = $this->getProfile();
        if ($profile->getId() !== $saveSearch->getProfile()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $this->em->remove($saveSearch);
        $this->em->flush();
        $searches = $this->listSavedSearches();
        $data = $this->render('TheaterjobsMainBundle:Partial:list.html.twig', ['searches' => $searches])->getContent();

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @Route("/index", name="tj_saved_searches_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listSavedSearchesAction()
    {
        return $this->render('TheaterjobsMainBundle:Search:list.html.twig', [
            'searches' => $this->listSavedSearches()
        ]);
    }

    /**
     * List saved searches based on profile with finder
     * @return mixed
     */
    private function listSavedSearches()
    {
        $profile = $this->getProfile();
        $results = $this->em->getRepository(SaveSearch::class)->searchesByPeopleId($profile->getId());
        $this->getTagNames($results);
        return $this->sortSearches($results);
    }

    /**
     * Get tag names from tag params saved as json
     * @param array $searches
     */
    private function getTagNames($searches)
    {
        /** @var SaveSearch $search */
        foreach ($searches as $search) {
            $params = $this->saveSearch->getParamsArr($search);
            $search->setParamsArr($params);
        }
    }

    /**
     * :(
     * Get tag names from tag params saved as json
     * @param SaveSearch[] $searches
     * @return array
     */
    private function sortSearches($searches)
    {
        $profile = [];
        $orga = [];
        $job = [];
        $news = [];
        /** @var SaveSearch $search */
        foreach ($searches as $search) {
            if ($search->getShortEntity() == 'profile') {
                $profile[] = $search;
            } else if ($search->getShortEntity() == 'news') {
                $news[] = $search;
            } else if ($search->getShortEntity() == 'job') {
                $job[] = $search;
            } else if ($search->getShortEntity() == 'organization') {
                $orga[] = $search;
            }
        }
        return array_merge(array_merge($profile, $orga), array_merge($job, $news));
    }


    /**
     * @Route("/notifications/subscribe/{id}", name="tj_saved_searches_check_notify", options={"expose"=true})
     * @Method("GET")
     * @param SaveSearch $saveSearch
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkNotify(SaveSearch $saveSearch)
    {
        return $this->updateNotificationCheck($saveSearch , SaveSearch::ONCE_A_DAY);
    }

    /**
     * @Route("/notifications/unsubscribe/{id}", name="tj_saved_searches_list_uncheck_notify", options={"expose"=true})
     * @Method("GET")
     * @param SaveSearch $saveSearch
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function uncheckNotify(SaveSearch $saveSearch)
    {
        return $this->updateNotificationCheck($saveSearch , SaveSearch::NEVER);
    }

    /**
     * @param $saveSearch
     * @param $status
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateNotificationCheck($saveSearch, $status){

        $saveSearch->setNotification($status);

        $this->em->persist($saveSearch);
        $this->em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }

}
