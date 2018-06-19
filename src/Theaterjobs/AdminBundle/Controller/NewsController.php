<?php

namespace Theaterjobs\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * UserOrganization controller.
 *
 * @Route("/news")
 */
class NewsController extends BaseController
{
    /**
     * Confirms a reply
     *
     * @Route("/confirm/news/{status}/{slug}", name="tj_admin_confirm_news", options={"expose"=true} )
     * @ParamConverter("news", options={"mapping": {"slug": "slug"}})
     * @Method("GET")
     * @param Request $request
     * @param News $news
     * @param $status
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function confirmNewsAction(Request $request, $status, News $news)
    {
        if ($request->isXmlHttpRequest()) {
            $param = 'error';
            $val = 0;
            if ($status == 'true') {
                $val = 1;
                $text = $this->getTranslator()->trans('admin.news.bootbox.newsPublished');
                $param = 'publish';
            } elseif ($status == 'false') {
                $text = $this->getTranslator()->trans('admin.news.bootbox.newsUnpublished');
                $param = 'unpublish';
            }
            $news->setPublished($val);
            $news->setPublishAt($status == 'true' ? new \DateTime() : null);
            $news->setUnPublishAt($status == 'true' ? null : new \DateTime());
            $news->setArchived($status == 'true' ? false : true);
            $this->getEM()->persist($news);
            $this->getEM()->flush();

            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($news, $this->getTranslator()->trans('tj.user.activity.news.confirmed', [], 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);
            $response = new JsonResponse([
                $param => true,
                'text' => $text
            ]);

            return $response;
        }

        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * Deletes a News entity.
     *
     * @Route("/delete/{slug}", name="tj_admin_news_delete")
     * @Method("GET")
     * @param News $news
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @internal param $slug
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteNewsAction(News $news)
    {
        if (!$news) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $this->getEM()->remove($news);
        $this->getEM()->flush();

        $this->addFlash('newsIndex', ['success' => $this->getTranslator()->trans("flash.success.news.deleted")]);

        return $this->redirect($this->generateUrl('tj_news'));
    }
}