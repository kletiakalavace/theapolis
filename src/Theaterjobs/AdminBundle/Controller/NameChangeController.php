<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\AdminBundle\Form\NameChangeRequestType;
use Theaterjobs\AdminBundle\Model\NameChangeRequestSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Entity\NameChangeRequest;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * AccountSettings controller.
 * @Route("/name-change")
 */
class NameChangeController extends BaseController
{
    /**
     * Lists all pending.
     *
     * @Route("/pending", name="tj_admin_pending_name_change_requests")
     * @Method("GET")
     *
     */
    public function pendingAction()
    {
        $nameChangeRequestSearch = new NameChangeRequestSearch();

        $adminNameChangeRequestSearchForm = $this->createGeneralSearchForm(NameChangeRequestType::class,
            $nameChangeRequestSearch,
            ['choices' => $this->formTypeRequestsChoices()],
            'admin_load_creators_index');

        return $this->render('TheaterjobsAdminBundle:NameChangeRequests:list.html.twig', [
                'form' => $adminNameChangeRequestSearchForm->createView()
            ]
        );
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load-pending-name-change-requests", name="tj_admin_load_pending_name_change_requests",options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function loadNameChangeRequest(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $nameChangeRequestSearch = new NameChangeRequestSearch();

        $adminNameChangeRequestSearchForm = $this->createGeneralSearchForm(NameChangeRequestType::class,
            $nameChangeRequestSearch,
            ['choices' => $this->formTypeRequestsChoices()],
            'admin_load_creators_index');

        $adminNameChangeRequestSearchForm->handleRequest($request);
        $adminNameChangeRequestSearch = $adminNameChangeRequestSearchForm->getData();

        $hideActionsColumn = false;

        $nameChangeRequests = $this->getEM()->getRepository(NameChangeRequest::class)->adminListSearch($adminNameChangeRequestSearch);

        $paginator = $this->getPaginator();

        $paginatedNameChangeRequests = $paginator->paginate($nameChangeRequests,
            $pageNr,
            $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedNameChangeRequests->getTotalItemCount();

        foreach ($paginatedNameChangeRequests as $changeRequest) {
            $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig',
                [
                    'date' => $changeRequest['createdAt']
                ]
            )->getContent();

            if ($hideActionsColumn) {
                $actionsColumn = 'Not Available.';
            } else {
                $actionsColumn =
                    '<div class="form-group name-change-request-action-group ' . $hideActionsColumn . '">
                    <a href="javascript:;" class="name-change-request-action circle-green"  onclick="requestApprove(' . $changeRequest['id'] . ')">
                       <i class="fa fa-check" aria-hidden="true"></i> 
                    </a>
                    <a href="javascript:;" class="name-change-request-action circle-red"  onclick="requestDenied(' . $changeRequest['id'] . ')">
                       <i class="fa fa-times" aria-hidden="true"></i>
                    </a>
                </div>';
            }

            $records["data"][] =
                [
                    $date,
                    '<a href="' . $this->generateUrl('tj_profile_profile_show', ['slug' => $changeRequest['slug']]) . '">' . $changeRequest['email'],
                    $changeRequest['oldName'],
                    $changeRequest['newName'],
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
     * Confirms a name change request.
     *
     * @Route("/confirm/{id}", name="tj_admin_confirm_name_change_requests", options={"expose"=true})
     * @Method("GET")
     * @param NameChangeRequest $nameChangeRequest
     * @return JsonResponse
     */
    public function confirmAction(NameChangeRequest $nameChangeRequest)
    {
        if (!$nameChangeRequest) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Request could not be found.'
            ]);
        }

        $user = $this->getUser();
        $profile = $nameChangeRequest->getCreatedBy()->getProfile();
        $nameChangeRequest->setStatus(NameChangeRequest::APPROVED);
        $nameChangeRequest->setUpdatedBy($user);
        $nameChangeRequest->setUpdatedAt(Carbon::now());

        if ($nameChangeRequest->getNewFirstName() != null) {
            $profile->setFirstName($nameChangeRequest->getNewFirstName());
        }
        if ($nameChangeRequest->getNewLastName() != null) {
            $profile->setLastName($nameChangeRequest->getNewLastName());
        }

        $this->getEM()->persist($profile);
        $this->getEM()->persist($nameChangeRequest);
        $this->getEM()->flush();

        $mailer = $this->get('app.mailer.twig_swift');
        $mailer->nameChangeRequestManagement('confirmed', $nameChangeRequest->getCreatedBy());

        //Send notification to user
        $title = 'dashboard.notification.nameChange.approved %new_firstname% %new_lastname%';
        $description = 'dashboard.notification.nameChange.approved.description %new_firstname% %new_lastname%';

        $transParams = [
            '%new_firstname%' => $profile->getFirstName(),
            '%new_lastname%' => $profile->getLastName()
        ];
        $link = 'tj_user_account_settings';
        $linkParams = ['slug' => $profile->getSlug()];

        $notification = (new Notification())
            ->setTitle($title)
            ->setDescription($description)
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setTranslationKeys($transParams)
            ->setLink($link)
            ->setLinkKeys($linkParams);

        $event = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($profile->getUser()->getId())
            ->setNotification($notification)
            ->setFrom($user)
            ->setUsers($profile->getUser())
            ->setType('name_change_approved');

        $this->get('event_dispatcher')->dispatch('notification', $event);

        return new JsonResponse([
            'error' => false,
            'message' => 'success'
        ]);
    }


    /**
     * Rejects a name change request.
     *
     * @Route("/reject/{id}", name="tj_admin_reject_name_change_requests", options={"expose"=true})
     * @Method("GET")
     * @param NameChangeRequest $nameChangeRequest
     * @return JsonResponse
     */
    public function rejectAction(NameChangeRequest $nameChangeRequest)
    {
        if (!$nameChangeRequest) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Request could not be found.'
            ]);
        }

        $user = $this->getUser();
        $profile = $nameChangeRequest->getCreatedBy()->getProfile();
        $nameChangeRequest->setStatus(NameChangeRequest::REJECTED);
        $nameChangeRequest->setUpdatedBy($user);
        $nameChangeRequest->setUpdatedAt(Carbon::now());

        $this->getEM()->persist($nameChangeRequest);
        $this->getEM()->flush();
        $mailer = $this->get('app.mailer.twig_swift');
        $mailer->nameChangeRequestManagement('rejected', $nameChangeRequest->getCreatedBy());

        //Send notification to user
        $title = 'dashboard.notification.nameChange.rejected %old_firstname% %old_lastname%';
        $description = 'dashboard.notification.nameChange.rejected.description';

        $transParams = [
            '%old_firstname%' => $profile->getFirstName(),
            '%old_lastname%' => $profile->getLastName()
        ];
        $link = 'tj_user_account_settings';
        $linkParams = ['slug' => $profile->getSlug()];


        $notification = new Notification();
        $notification->setTitle($title)
            ->setDescription($description)
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setLink($link)
            ->setLinkKeys($linkParams)
            ->setTranslationKeys($transParams);

        $event = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($profile->getUser()->getId())
            ->setNotification($notification)
            ->setFrom($user)
            ->setUsers($profile->getUser())
            ->setType('name_change_rejected');

        $this->get('event_dispatcher')->dispatch('notification', $event);

        return new JsonResponse([
            'error' => false,
            'message' => 'success'
        ]);
    }

    /**
     * @return array
     */
    protected function formTypeRequestsChoices()
    {
        return $dataCount = [
            $this->getTranslator()->trans('admin.nameChange.select.Pending', [], 'messages') => NameChangeRequest::PENDING,
            $this->getTranslator()->trans('admin.nameChange.select.Approved', [], 'messages') => NameChangeRequest::APPROVED,
            $this->getTranslator()->trans('admin.nameChange.select.Rejected', [], 'messages') => NameChangeRequest::REJECTED
        ];
    }
}
