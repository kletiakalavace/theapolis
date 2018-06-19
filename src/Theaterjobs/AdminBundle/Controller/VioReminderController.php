<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Entity\VioDone;
use Theaterjobs\AdminBundle\Entity\VioReminder;
use Theaterjobs\AdminBundle\Form\VioReminderSearchType;
use Theaterjobs\AdminBundle\Model\VioReminderSearch;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * VioReminder controller.
 *
 * @Route("/vio-reminder")
 *
 */
class VioReminderController extends BaseController
{

    /**
     * Lists all VioReminder entities.
     *
     * @Route("/index", name="admin_vio_reminder_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $vioReminderSearch = new VioReminderSearch();

        $adminVioReminderSearchForm = $this->createGeneralSearchForm(VioReminderSearchType::class,
            $vioReminderSearch,
            [],
            'tj_admin_load_vio_reminder'
        );

        return $this->render('TheaterjobsAdminBundle:VioReminder:index.html.twig', [
            'form' => $adminVioReminderSearchForm->createView()
        ]);
    }


    /**
     * Lists all confirmed.
     *
     * @Route("/load-vio-reminder", name="tj_admin_load_vio_reminder", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadVioReminder(Request $request)
    {
        $em = $this->getEM();
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $vioReminderSearch = new VioReminderSearch();

        $adminVioReminderSearchForm = $this->createGeneralSearchForm(VioReminderSearchType::class,
            $vioReminderSearch,
            [],
            'tj_admin_load_vio_reminder'
        );

        $adminVioReminderSearchForm->handleRequest($request);
        $adminVioReminderSearch = $adminVioReminderSearchForm->getData();

        $viosReminder = $em->getRepository(VioReminder::class)->adminListSearch($adminVioReminderSearch);

        $paginator = $this->getPaginator();
        $paginatedViosReminder = $paginator->paginate($viosReminder, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedViosReminder->getTotalItemCount();

        foreach ($paginatedViosReminder as $vioReminder) {
            $organizationColumn = sprintf('<a href=%s>%s</a>',
                $this->generateUrl('tj_organization_show',
                    [
                        'slug' => $vioReminder['organizationSlug']
                    ]
                ),
                $vioReminder['organization']);

            $today = Carbon::today();
            $created = new Carbon($vioReminder['createdAt']->format('Y-m-d'));

            $toSentColumn = $today->diffInDays($created) . " days ago";
            $commentColumn = sprintf('<div class="form-group"><textarea id="comment-%s"></textarea></div>', $vioReminder['id']);
            $createdAtColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $vioReminder['createdAt']])->getContent();
            $actionColumn = sprintf('<button class="btn btn-success save" onclick="vioReminderSave(\'%s\')">Save</button>', $vioReminder['id']);

            $records["data"][] = [
                $organizationColumn,
                $toSentColumn,
                $commentColumn,
                $createdAtColumn,
                $actionColumn
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }


    /**
     * Creates a new VioDone entity.
     *
     * @Route("/done/{id}", name="admin_vio_reminder_done", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @param VioReminder $vioReminder
     * @return JsonResponse
     */
    public function doneAction(Request $request, VioReminder $vioReminder)
    {
        $comment = $request->request->get('comment');
        $vio = $vioReminder->getVio();
        if ($vio->getisChecked()) {
            $vioDone = new VioDone();
            $em = $this->getEM();
            $vioDone->setOrganization($vio->getOrganization());
            $vioDone->setProfile($this->getUser()->getProfile());
            $vioDone->setComment($comment);
            $vio->setIschecked(false);
            $em->persist($vio);
            $em->persist($vioDone);
            $em->remove($vioReminder);
            $em->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false]);
    }
}
