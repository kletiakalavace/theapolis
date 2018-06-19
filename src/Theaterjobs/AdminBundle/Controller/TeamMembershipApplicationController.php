<?php

namespace Theaterjobs\AdminBundle\Controller;


use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\AdminBundle\Form\TeamMembershipSearchType;
use Theaterjobs\AdminBundle\Model\AdminTeamMembershipSearch;
use Theaterjobs\InserateBundle\Entity\TeamMembershipApplication;
use Theaterjobs\MainBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Entity\UserOrganization;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * The Membership Controller.
 *
 * Provides the Overview of the Memberships available
 *
 * @category Controller
 * @Route("/team-membership-applications")
 */
class TeamMembershipApplicationController extends BaseController
{
    /**
     * Lists all pending team membership applications.
     * @Route("/pending", name="tj_admin_pending_membership_applications")
     * @Method("GET")
     *
     */
    public function pendingMembershipApplicationsAction()
    {
        $adminTeamMembershipSearch = new AdminTeamMembershipSearch();

        $adminTeamMembershipSearchForm = $this->createGeneralSearchForm(TeamMembershipSearchType::class,
            $adminTeamMembershipSearch,
            [],
            'admin_pending_membership_applications'
        );

        return $this->render('TheaterjobsAdminBundle:TeamMembership:pending.html.twig', [
            'form' => $adminTeamMembershipSearchForm->createView()
        ]);
    }

    /**
     * Lists all confirmed.
     *
     * @Route("/load_pending_team_membership_applications", name="admin_pending_membership_applications", options={"expose" = true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function loadPendingMembershipApplications(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $adminTeamMembershipSearch = new AdminTeamMembershipSearch();

        $adminTeamMembershipSearchForm = $this->createGeneralSearchForm(TeamMembershipSearchType::class,
            $adminTeamMembershipSearch,
            [],
            'admin_pending_membership_applications'
        );

        $adminTeamMembershipSearchForm->handleRequest($request);
        $adminTeamMembershipSearch = $adminTeamMembershipSearchForm->getData();

        $pendingApplications = $this->getEM()->getRepository(TeamMembershipApplication::class)->adminListTeamMembershipApplications($adminTeamMembershipSearch);
        $paginator = $this->getPaginator();

        $paginatedApplications = $paginator->paginate($pendingApplications, $pageNr, $rows);
        $iTotalRecords = $paginatedApplications->getTotalItemCount();
        $records = [];
        $records["data"] = [];

        foreach ($paginatedApplications as $pendingApplication) {

            $dateColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig',
                [
                    'date' => $pendingApplication['createdAt']
                ]
            )->getContent();

            $organizationColumn = sprintf('<a target="_blank" href=%s>%s</a>',
                $this->generateUrl('tj_organization_show',
                    [
                        'slug' => $pendingApplication['organizationSlug']
                    ]
                ),
                $pendingApplication['organization']);


            $userColumn = sprintf('<a target="_blank" href=%s>%s</a>',
                $this->generateUrl('tj_profile_profile_show',
                    [
                        'slug' => $pendingApplication['profileSlug']
                    ]
                ),
                $pendingApplication['user']);

            $viewButton = sprintf('<a  data-target="#myModal" data-hash="viewApplication"  data-toggle="modal" data-color="#87162D" class="btn btn-primary"  href=%s>%s</a>',
                $this->generateUrl('admin_pending_membership_view_application',
                    [
                        'id' => $pendingApplication['id']
                    ]
                ),
                $this->getTranslator()->trans('admin.pendingTeamMembershipApp.button.View', [], 'messages'));

            $confirmButton = sprintf('<button  class="btn btn-primary" onclick="applicationConfirm(this)"  data-id=%s>%s</a>',
                $pendingApplication['id'],
                $this->getTranslator()->trans('admin.pendingTeamMembershipApp.button.Confirm', [], 'messages'));

            $rejectButton = sprintf('<button  class="btn btn-primary" onclick="applicationReject(this)"  data-id=%s>%s</a>',
                $pendingApplication['id'],
                $this->getTranslator()->trans('admin.pendingTeamMembershipApp.button.Reject', [], 'messages'));

            $records["data"][] = [
                $dateColumn,
                $userColumn,
                $organizationColumn,
                "<div class='btn-group btn-group-sm btn-pend-team'>" . $viewButton . ' ' . $confirmButton . ' ' . $rejectButton . '</div>'
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * Lists all pending team membership applications.
     * @Route("/view_membership_application/{id}", name="admin_pending_membership_view_application")
     * @Method("GET")
     * @param TeamMembershipApplication $teamMembershipApplication
     * @return \Symfony\Component\HttpFoundation\Response*
     *
     */
    public function viewPendingMembershipApplicationsAction(TeamMembershipApplication $teamMembershipApplication)
    {
        return $this->render('TheaterjobsAdminBundle:Organization:viewPendingTeamMembershipApplication.html.twig', [
            'entity' => $teamMembershipApplication
        ]);
    }

    /**
     * Lists all pending team membership applications.
     * @Route("/confirm/{id}", name="admin_pending_membership_confirm_application", options={"expose"=true})
     * @Method("GET")
     * @param TeamMembershipApplication $teamMembershipApplication
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function confirmPendingMembershipApplicationsAction(TeamMembershipApplication $teamMembershipApplication)
    {
        if (!$teamMembershipApplication) {
            return new JsonResponse(['error' => true, "message" => "Team Membership not found"]);
        }


        if (!$teamMembershipApplication->isPending()) {
            return new JsonResponse(['error' => true, "message" => "Team Membership already checked"]);
        }
        $organization = $teamMembershipApplication->getOrganization();
        $creator = $teamMembershipApplication->getUser();

        $allUsers = $this->em->getRepository('TheaterjobsUserBundle:UserOrganization')->countActiveMembers($organization->getId());
        if ($allUsers >= UserOrganization::TEAM_MEMBER_LIMIT) {
            $err = $this->get('translator')->trans('user.userOrganization.members.limit.reached', [], 'forms');
            return new JsonResponse([ 'error' => true, "message" => $err ]);
        }

        if (!$organization->isTeamMember($creator)) {

            $teamMembershipApplication->setPending(false);
            $teamMembershipApplication->setDeleted(false);
            $this->getEM()->persist($teamMembershipApplication);
            $this->getEM()->flush();

            $ent = $this->getEM()->getRepository(UserOrganization::class)->findOneBy(
                [
                    'user' => $teamMembershipApplication->getUser(),
                    'organization' => $organization
                ]
            );

            if ($ent) {
                if ($ent->getRevokedAt()) {
                    $ent->setRevokedAt(null);
                    $ent->setGrantedAt(Carbon::now());
                    $this->getEM()->persist($ent);
                    $this->getEM()->flush();
                } else {
                    return new JsonResponse(
                        [
                            'error' => true,
                            "message" => $this->get('translator')->trans('user.organization.alreadymember', [], 'forms')
                        ]
                    );
                }
            } else {
                $newTeamMember = new UserOrganization();
                $this->addTeamMemberToOrganization($newTeamMember, $creator, $organization, $teamMembershipApplication->getCreatedAt());
            }

            $dispatcher = $this->get('event_dispatcher');
            $title = $this->getTranslator()->trans('dashboard.notification.organization.teamMembership.approved %organizationName%', [], 'messages');
            $description = $this->getTranslator()->trans('dashboard.notification.organization.teamMembership.approved.description %organizationName%', [], 'messages');
            $transParams = ['%organizationName%' => $organization->getName()];
            $transDescParams = ['%organizationName%' => $organization->getName()];

            $link = 'tj_organization_show';
            $linkParams = ['slug' => $organization->getSlug()];

            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription($description)
                ->setTranslationDescKeys($transDescParams)
                ->setCreatedAt(Carbon::now())
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $notificationEvent = (new NotificationEvent())
                ->setObjectClass(User::class)
                ->setObjectId($creator->getId())
                ->setNotification($notification)
                ->setFrom($creator)
                ->setUsers($creator)
                ->setType('team_membership_request_approved');

            $dispatcher->dispatch('notification', $notificationEvent);

            $uacEvent = new UserActivityEvent(
                $organization,
                $this->getTranslator()->trans('user.activity.organization.teamMembership.approved', [], 'activity') . ' ' . $creator->getProfile()->getFullName(), false
            );
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            return new JsonResponse(
                [
                    'error' => false,
                    "message" => $this->getTranslator()->trans('admin.confirm.user.succes.teamMembershipApplication', [])
                ]
            );
        } else {
            return new JsonResponse(
                [
                    'error' => true,
                    "message" => $this->getTranslator()->trans('admin.confirm.user.alreadymember.teamMembershipApplication', [])
                ]
            );
        }

    }

    /**
     * Lists all pending team membership applications.
     * @Route("/reject/{id}", name="admin_pending_membership_reject_application", options={"expose"=true})
     * @Method("GET")
     * @param TeamMembershipApplication $teamMembershipApplication
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function rejectPendingMembershipApplicationsAction(TeamMembershipApplication $teamMembershipApplication)
    {
        if (!$teamMembershipApplication) {
            return new JsonResponse(['error' => true, "message" => "Team Membership Application not found"]);
        }
        if (!$teamMembershipApplication->isPending()) {
            return new JsonResponse(['error' => true, "message" => "Team Membership already checked"]);
        }

        $teamMembershipApplication->setPending(false);
        $teamMembershipApplication->setDeleted(true);
        $this->getEM()->persist($teamMembershipApplication);
        $this->getTranslator()->flush();

        return new JsonResponse(
            [
                'error' => false,
                'message' => $this->getTranslator()->trans("admin.teammembershipapplication.removed.Successfully", [])
            ]
        );
    }

    /**
     * @param UserOrganization $newTeamMember
     * @param User $user
     * @param Organization $organization
     * @param null $requestedAt
     * @return void
     */
    private function addTeamMemberToOrganization(UserOrganization $newTeamMember, User $user, Organization $organization, $requestedAt = null)
    {
        $newTeamMember->setOrganization($organization);
        $newTeamMember->setUser($user);

        if ($requestedAt == null) {
            $requestedAt = Carbon::now();
        }

        $newTeamMember->setRequestedAt($requestedAt);

        $newTeamMember->setGrantedAt(Carbon::now());
        $this->getEM()->persist($newTeamMember);
        $this->getEM()->flush();
    }

}