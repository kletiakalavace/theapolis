<?php

namespace Theaterjobs\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Acl\Exception\Exception;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Form\OrganizationType;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Entity\UserOrganization;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\InserateBundle\Entity\Organization;
use Carbon\Carbon;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Entity\Notification;

/**
 * UserOrganization controller.
 *
 * @Route("/user-organization")
 */
class UserOrganizationController extends BaseController
{
    use ESUserActivity;

    /**
     * Creates a new UserOrganization entity.
     *
     * @Route("/create/{slug}", name="tj_main_user_organization_create", condition="request.isXmlHttpRequest()", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request, Organization $organization)
    {
        $authUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && !$organization->isTeamMember($authUser)) {
            //Not member
            $err = $this->get('translator')->trans('user.userOrganization.notAllowed', [], 'forms');
            return new JsonResponse([
                'errors' => [ 'user' => $err ]
            ]);
        }
        if (!$organization->isActive()) {
            $err = $this->get('translator')->trans('user.userOrganization.notActiveOrganization.noNew.members', [], 'forms');
            //Admin is not allowed to add members to organization with pending status
            return new JsonResponse([ 'errors' => [ 'user' => $err ] ]);
        }
        $em = $this->getEM();
        $orgaId = $organization->getId();
        $allUsers = $em->getRepository('TheaterjobsUserBundle:UserOrganization')->countActiveMembers($orgaId);
        if ($allUsers >= UserOrganization::TEAM_MEMBER_LIMIT) {
            $err = $this->get('translator')->trans('user.userOrganization.members.limit.reached', [], 'forms');
            //Admin is not allowed to add members to organization with pending status
            return new JsonResponse([ 'errors' => [ 'user' => $err ] ]);
        }
        // Build validation form for UserOrganization and validate with current request data
        $entity = new UserOrganization();
        $entity->setOrganization($organization);
        // Create Form
        $routeOpts = ['slug' => $organization->getSlug()];
        $routeName = "tj_main_user_organization_create";
        $formName = "theaterjobs_userbundle_userorganization";
        $form = $this->createCreateForm($formName, $entity, [], $routeName, $routeOpts);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var User $user */
            $user = $form->get('user')->getData();
            $userOrganization = $organization->getTeamMember($user);
            if (!$userOrganization) {
                $entity->setUser($user);
                $entity->setRequestedAt(Carbon::now());
                $entity->setGrantedAt(Carbon::now());
                $em->persist($entity);

                $opts = ['%user%' => $user->getProfile()->defaultName()];
                $descr = $this->getTranslator()->trans('organization.activity.label.addedMember %user%', $opts, 'activity');
                $this->logUserActivity($organization, $descr);
            } else {
                //User has been connected to this organization but deactivated
                $disabled = $this->enableRightsAction($userOrganization);
                //Check if user is member of organization
                if (!$disabled) {
                    $err = $this->getTranslator()->trans('user.organization.alreadymember', [], 'forms');
                    return new JsonResponse(['errors' => ['user' => $err] ]);
                }
            }
            // Get user list
            $canEdit = $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($authUser);
            $content = $this->render('TheaterjobsInserateBundle:Partial:organizationMembers.html.twig', ['entity' => $organization, 'canEdit' => $canEdit,]);
            // Get organization logs
            $activity = $this->getESUserActivity(Organization::class, $orgaId);
            $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', ['entity' => $organization, 'activity' => $activity]);
            //Send email to new user of the organization
            $this->get('app.mailer.twig_swift')->newOrganizationMember($organization, $user);
            //Send notifications to user/team members
            //excluded users for notification
            $ids = [ $user->getId(), $authUser->getId() ];
            $allUsers = $this->getEM()->getRepository(UserOrganization::class)->findAllUsers($orgaId, $em, $ids);
            $options = [
                'action' => 'addMember',
                'user' => $user,
                'allUsers' => $allUsers,
                'organization' => $organization,
            ];
            $this->sendNotifications($options);
            $em->flush();
            return new JsonResponse([
                'content' => $content->getContent(),
                'logs' => $logs->getContent(),
                'message' => $this->get('translator')
                    ->trans('user.member.successfullyAdded', [], 'messages')
            ]);
        }
        return new JsonResponse([
            'formErrors' => $this->getErrorMessagesAJAX($form)
        ]);
    }

    /**
     * Displays a form to create a new UserOrganization entity.
     *
     * @Route("/new/{slug}", name="tj_main_user_organization_new" , condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param Organization $organization
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Organization $organization)
    {

        if ($this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser())) {

            $entity = new UserOrganization();
            $entity->setOrganization($organization);
            $entity->setUser();
            $routeOpts = ['slug' => $organization->getSlug()];
            $routeName = "tj_main_user_organization_create";
            $formName = "theaterjobs_userbundle_userorganization";
            $form = $this->createCreateForm($formName, $entity, [], $routeName, $routeOpts);

            return $this->render('TheaterjobsInserateBundle:Modal:newMemberOrganization.html.twig', [
                'entity' => $entity,
                'form' => $form->createView(),
            ]);
        }
        //Not teamMember
        return new JsonResponse([
            'errors' => [
                'user' => $this->get('translator')->trans('user.userOrganization.notAllowed', [], 'forms')
            ]
        ]);
    }

    /**
     * Revokes a user from organization
     *
     * @Route("/delete/{orgaSlug}/{userId}", name="tj_user_organization_delete", options={"expose"=true})
     *
     * @Method("GET")
     *
     * @param UserOrganization $entity
     *
     * @ParamConverter("entity", class="TheaterjobsUserBundle:UserOrganization", options={
     *    "repository_method" = "findByUserOrga",
     *    "map_method_signature" = true
     * })
     *
     * @return JsonResponse
     */
    public function revokeRightsAction(UserOrganization $entity)
    {
        $organization = $entity->getOrganization();
        $user = $entity->getUser();
        $em = $this->getEM();
        $canEdit = $this->isGranted('ROLE_ADMIN') || $organization->isTeamMember($this->getUser());
        if (!$canEdit) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->get('translator')->trans('organization.members.message.userNotAllowed', [], 'messages'),
            ]);
        }
        //Remove user from organization
        $entity->setRevokedAt(Carbon::now());
        $organization->addUserOrganization($entity);
        //Log this activity
        $profile = $user->getProfile();
        $translator = $this->get('translator');
        $msgOpts = ['%user%' => $profile->getFirstName() . ' ' . $profile->getLastName()];
        $msg = $translator->trans('organization.activity.label.removedUserFromOrganization %user%', $msgOpts, 'activity');
        $this->logUserActivity($organization, $msg, false, null, null, false);
        //Send notifications to user/team members
        //excluded users for notification
        $ids = [$user->getId(), $this->getUser()->getId()];
        $allUsers = $em->getRepository('TheaterjobsUserBundle:UserOrganization')->findAllUsers($organization->getId(), $em, $ids);
        $selfRemoved = !$this->isGranted('ROLE_ADMIN') && $this->getUser()->getId() == $user->getId();
        $options = [
            'action' => 'revokeMember',
            'user' => $user,
            'allUsers' => $allUsers,
            'organization' => $organization,
            'selfRevoked' => $selfRemoved
        ];
        $this->sendNotifications($options);
        $this->getEM()->flush();
        $activity = $this->getESUserActivity(Organization::class, $organization->getId());
        $logs = $this->render('TheaterjobsInserateBundle:Partial:organizationLogs.html.twig', [
            'entity' => $organization,
            'activity' => $activity
        ]);
        $content = $this->render('TheaterjobsInserateBundle:Partial:organizationMembers.html.twig', [
            'entity' => $organization,
            'canEdit' => $canEdit,
        ]);
        return new JsonResponse([
            'success' => true,
            'message' => $this->get('translator')->trans('organization.members.message.userRemoved', [], 'messages'),
            'content' => $content->getContent(),
            'logs' => $logs->getContent(),
            'selfRemoved' => $selfRemoved
        ]);

    }

    /**
     * @param UserOrganization $userOrganization
     * @return bool
     */
    private function enableRightsAction(UserOrganization $userOrganization)
    {
        //user is already member of organization and not revoked
        if (!$userOrganization->getRevokedAt()) {
            return false;
        }
        $userOrganization->setRevokedAt(null);
        $userOrganization->setGrantedAt(Carbon::now());

        $organization = $userOrganization->getOrganization();
        $profile = $userOrganization->getUser()->getProfile();
        $transOpts = ['%user%' => sprintf('%s %s', $profile->getFirstName(), $profile->getLastName())];
        $descr = $this->getTranslator()->trans('organization.activity.label.grantedPermissionsUser %user%', $transOpts, 'activity');

        $this->logUserActivity($organization, $descr, false, null, null, false);
        return true;
    }

    /**
     * Send notifications for revoked/added Members
     *
     * @param $options ['action', 'user', 'allUsers', 'selfRevoked = null']
     */
    private function sendNotifications($options)
    {
        $user = $options['user'];
        $organization = $options['organization'];
        $users = $options['allUsers'];
        //Revoke action
        if ($options['action'] == 'revokeMember') {
            $selfRevoked = $options['selfRevoked'];
            //There is nobody to send the notification
            if (!$users && !$selfRevoked) {
                $this->userNotification($options);
                return;
            }
            //User has removed himself
            if ($selfRevoked) {
                $this->selfRevokedNotifications($user, $organization, $users);
                //User has been removed from others
            } else {
                $this->userRevokedNotification($user, $organization, $users);
                //Send notification to the user
                $this->userNotification($options);
            }
        } else if ($options['action'] == 'addMember') {
            $this->addMemberNotifications($user, $organization, $users);
        }
    }

    /**
     * Send notifications to members/user x if user x is added
     *
     * @param $user
     * @param $organization
     * @param $users
     */
    private function addMemberNotifications($user, $organization, $users)
    {
        if (count($users) > 0) {
            //Send Notification to all organization members
            $added = $user->getProfile()->defaultName();
            $adder = $this->getProfile()->defaultName();

            $title = 'dashboard.notification.teamMember.addMember %adder% %added% %organization%';
            $transParams = array(
                '%adder%' => $adder,
                '%added%' => $added,
                '%organization%' => $organization->getName()
            );

            $link = 'tj_organization_show';
            $linkParams = array(
                'slug' => $organization->getSlug()
            );

            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $event = (new NotificationEvent())
                ->setObjectClass(Organization::class)
                ->setObjectId($organization->getId())
                ->setNotification($notification)
                ->setFrom($this->getUser())
                ->setUsers($users)
                ->setType('organization_add_member')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $event);
        }

        //Send notification to user being added
        $options = [
            'user' => $user,
            'action' => 'addMember',
            'organization' => $organization
        ];

        //In case user is Admin and can add users even if its not member
        if ($this->getUser()->getId() !== $user->getId()) {
            $this->userNotification($options);
        }
    }

    /**
     * Send notification to all members that user x revoked himself
     *
     * @param $user
     * @param $organization
     * @param $users
     *
     */
    private function selfRevokedNotifications($user, $organization, $users)
    {
        if (count($users) > 0) {
            $defaultName = $user->getProfile()->defaultName();

            $title = 'dashboard.notification.teamMember.selfrevokedMember %revoked% %organization%';
            $transParams = array(
                '%revoked%' => $defaultName,
                '%organization%' => $organization->getName()
            );

            $link = 'tj_organization_show';
            $linkParams = array(
                'slug' => $organization->getSlug()
            );

            //Send Notification to all organization members
            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $event = (new NotificationEvent())
                ->setObjectClass(Organization::class)
                ->setObjectId($organization->getId())
                ->setNotification($notification)
                ->setUsers($users)
                ->setType('organization_revoke_member')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $event);
        }
    }

    /**
     * Send notification to all members that user y is revoked from x
     *
     * @param User $user
     * @param Organization $organization
     * @param User[] $users
     */
    private function userRevokedNotification($user, $organization, $users)
    {
        if (count($users)) {
            $revoked = $user->getProfile()->defaultName();
            $revoker = $this->getProfile()->defaultName();

            $title = 'dashboard.notification.teamMember.revokedMember %revoker% %revoked% %organization%';
            $transParams = array(
                '%revoker%' => $revoker,
                '%revoked%' => $revoked,
                '%organization%' => $organization->getName()
            );

            $link = 'tj_organization_show';
            $linkParams = array(
                'slug' => $organization->getSlug()
            );

            //Send Notification to all organization members
            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $event = (new NotificationEvent())
                ->setObjectClass(Organization::class)
                ->setObjectId($organization->getId())
                ->setNotification($notification)
                ->setFrom($this->getUser())
                ->setUsers($users)
                ->setType('organization_revoke_member')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $event);
        }
    }

    /**
     * Send the notification only to the user for add/revoke action
     *
     * @param $options ['action', 'user', 'organization']
     */
    private function userNotification($options)
    {
        $organization = $options['organization'];
        $user = $options['user'];

        if ($options['action'] == 'revokeMember') {
            $revoker = $this->getProfile()->defaultName();

            $title = 'dashboard.notification.teamMember.revokedMemberBy %revoker% %organization%';
            $transParams = array(
                '%revoker%' => $revoker,
                '%organization%' => $organization->getName()
            );

            $link = 'tj_organization_show';
            $linkParams = array(
                'slug' => $organization->getSlug()
            );

            //Send Notification to all organization members
            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $event = (new NotificationEvent())
                ->setObjectClass(Organization::class)
                ->setObjectId($organization->getId())
                ->setNotification($notification)
                ->setFrom($this->getUser())
                ->setUsers($user)
                ->setType('organization_revoke_member')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $event);

        } else if ($options['action'] == 'addMember') {
            $adder = $this->getProfile()->defaultName();

            $title = 'dashboard.notification.teamMember.addedMemberBy %adder% %organization%';
            $transParams = array(
                '%adder%' => $adder,
                '%organization%' => $organization->getName()
            );

            $link = 'tj_organization_show';
            $linkParams = array(
                'slug' => $organization->getSlug()
            );

            //Send Notification to all organization members
            $notification = new Notification();

            $notification->setTitle($title)
                ->setTranslationKeys($transParams)
                ->setDescription('')
                ->setRequireAction(false)
                ->setLink($link)
                ->setLinkKeys($linkParams);

            $event = (new NotificationEvent())
                ->setObjectClass(Organization::class)
                ->setObjectId($organization->getId())
                ->setNotification($notification)
                ->setFrom($this->getUser())
                ->setUsers($user)
                ->setType('organization_add_member')
                ->setFlush(false);

            $this->get('event_dispatcher')->dispatch('notification', $event);

        } else {
            throw new Exception('Unknown action on notification');
        }
    }
}
