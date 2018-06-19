<?php

namespace Theaterjobs\UserBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\OptimisticLockException;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Theaterjobs\InserateBundle\Entity\Education;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MembershipBundle\Entity\BillingAddress;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Event\MembershipExpiredEvent;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\UserBundle\Services\AccountSettings;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\AdminUserComments;
use Theaterjobs\UserBundle\Entity\NameChangeRequest;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\MarkNotificationAsReadEvent;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Event\UserActivityEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * AccountSettings controller.
 * @Route("/account/settings")
 */
class AccountSettingsController extends BaseController
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var ObjectManager
     */
    private $em;

    /** @DI\Inject("session") */
    private $session;

    /** @DI\Inject("theaterjobs_membership.sepa") */
    public $sepa;

    /**
     * @DI\Inject("theaterjobs_user_bundle.account_settings")
     * @var AccountSettings
     */
    public $accountSettings;

    /**
     * Show all account settings.
     *
     * @Route("/{slug}", name="tj_user_account_settings", defaults={"slug" = null})
     * @Method({"GET", "POST"})
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Profile $profile = null)
    {
        $profile = $this->isSetProfile($profile);
        $userId = $profile->getUser()->getId();
        $isOwner = $this->getUser()->isEqual($profile->getUser());
        $billingAddress = $profile->getBillingAddress();

        $activeChangeEmailRequest = $this->em->getRepository('TheaterjobsUserBundle:User')->checkForActiveChangeEmailRequest($userId);
        $activeChangeNameRequest = $this->em->getRepository('TheaterjobsUserBundle:User')->checkForActiveNameChangeRequest($userId);
        $membershipBlockData = $this->accountSettings->getMembershipBlockData($profile);

        $returnArgs = array_merge([
            'hasChangeEmailRequest' => $activeChangeEmailRequest,
            'hasChangeNameRequest' => $activeChangeNameRequest,
            'billingAddress' => $billingAddress,
        ], $membershipBlockData);

        // Override and check from controller
        $returnArgs['owner'] = $isOwner;

        if ($this->isGranted('ROLE_ADMIN')) {
            $options = $this->adminAccountSettings($profile);
            $returnArgs['options'] = $options;
        }
        return $this->render('TheaterjobsUserBundle:AccountSettings:index.html.twig', $returnArgs);
    }

    /**
     * @Route("/general-info-modal/{slug}", name="tj_profile_account_settings_generalInfoModal" , condition="request.isXmlHttpRequest()")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generalInfoModalAction(Profile $profile)
    {
        $getProfile = $this->isSetProfile($profile);

        $form = $this->createEditForm('tj_user_form_master_data', $getProfile, [], 'tj_user_account_settings_create_master_data', ['slug' => $getProfile->getSlug()]);

        return $this->render('TheaterjobsUserBundle:AccountSettings/Modal:profileGeneralInfo.html.twig', array(
                'entity' => $getProfile,
                'form' => $form->createView(),
            )
        );
    }


    /**
     * @Route("/billing-address/{slug}", name="tj_profile_account_settings_billing_address_modal")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function billingAddressAction(Profile $profile)
    {
        $getProfile = $this->isSetProfile($profile);

        $form = $this->createEditForm('tj_user_form_billing_address', $getProfile, [], 'tj_user_account_settings_update_billing_address', ['slug' => $getProfile->getSlug()]);

        return $this->render('TheaterjobsUserBundle:AccountSettings/Modal:billingAddress.html.twig', array(
                'entity' => $getProfile,
                'form' => $form->createView(),
            )
        );
    }


    /**
     * @Route("/reset-password-modal/{slug}", name="tj_profile_account_settings_resetPasswordModal",condition="request.isXmlHttpRequest()")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordAction(Profile $profile)
    {

        if ($profile === null) {
            $entity = $this->getUser();
        } else {
            $entity = $profile->getUser();
        }

        $form = $this->createCreateForm('tj_user_form_change_password', $entity, $options = [], 'tj_user_password_change_create');
        return $this->render('TheaterjobsUserBundle:AccountSettings/Modal:changePassword.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }


    /**
     * Updates a Profile entity.
     * @Route("/update/{slug}", name="tj_user_account_settings_create_master_data")
     * @Method({"PUT"})
     * @param Request $request
     * @param Profile $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, Profile $entity)
    {

        $oldFirstName = $entity->getFirstName();
        $oldLastName = $entity->getLastName();
        $profile = $this->isSetProfile($entity);

        $form = $this->createEditForm('tj_user_form_master_data', $profile, [], 'tj_user_account_settings_create_master_data', ['slug' => $profile->getSlug()]);
        $form->handleRequest($request);
        $requestedNameChange = false;
        $requestedNameMessage = '';

        if ($form->isValid()) {
            $profile->setProfileName(true);

            if ($form->get('subtitle')->getData() == '') {
                $profile->setSubtitle($oldFirstName . ' ' . $oldLastName);
            }


            $activeChangeNameRequest = $this->em->getRepository('TheaterjobsUserBundle:User')->checkForActiveNameChangeRequest($profile->getUser()->getId());

            if (count($activeChangeNameRequest) > 0) {
                $hasPendingNameChangeRequest = true;
                $requestedNameMessage = $this->getTranslator()->trans("flash.namechange.request.pending", array(), 'flashes');
            } else {
                $hasPendingNameChangeRequest = false;
            }

            if (($oldFirstName != $form->get('firstName')->getData()) or ($oldLastName != $form->get('lastName')->getData())) {
                $requestedNameChange = true;

                if (count($activeChangeNameRequest) == 0) {
                    $hasPendingNameChangeRequest = true;
                    $nameChangeRequest = new NameChangeRequest();
                    $nameChangeRequest->setOldFirstName($oldFirstName);
                    $nameChangeRequest->setNewFirstName($form->get('firstName')->getData());
                    $nameChangeRequest->setOldLastName($oldLastName);
                    $nameChangeRequest->setNewLastName($form->get('lastName')->getData());
                    $nameChangeRequest->setStatus(0);
                    $nameChangeRequest->setCreatedAt(new \DateTime());
                    $nameChangeRequest->setCreatedBy($profile->getUser());
                    $this->em->persist($nameChangeRequest);
                }

            }
            $profile->setFirstName($oldFirstName);
            $profile->setLastName($oldLastName);
            $this->em->persist($profile);
            $this->em->flush();

            $billingAddress = $profile->getBillingAddress();

            $generalInfoBox = $this->render('TheaterjobsUserBundle:AccountSettings:generalInfo.html.twig', array(
                'profile' => $profile,
                'billingAddress' => $billingAddress,
                'hasChangeNameRequest' => $hasPendingNameChangeRequest
            ));
            return new JsonResponse ([
                'error' => false,
                'requestedNameChange' => $requestedNameChange,
                'requestedNameMessage' => $requestedNameMessage,
                'data' => $generalInfoBox->getContent(),
                'newName' => $profile->defaultName()
            ]);
        }
        return new JsonResponse([
            'error' => true,
            'errors' => $this->getErrorMessagesAJAX($form)
        ]);
    }

    /**
     * Updates a Profile entity.
     * @Route("/update_form_type/{slug}", name="tj_user_account_settings_update_billing_address")
     * @Method("POST")
     * @param Request $request
     * @param Profile $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateBillingAddressAction(Request $request, Profile $entity)
    {
        $profile = $this->isSetProfile($entity);

        $form = $this->createEditForm('tj_user_form_billing_address', $profile, [], 'tj_user_account_settings_update_billing_address', ['slug' => $profile->getSlug()]);
        $form->bind($request);
        $requestedNameChange = false;
        $requestedNameMessage = '';
        if ($form->isValid()) {

            $billingAddress = $profile->getBillingAddress();

            if (!$billingAddress) {
                $billingAddress = new BillingAddress();
            }

            $billingAddress->setProfile($profile);
            $profile->setBillingAddress($billingAddress);

            $this->em->persist($billingAddress);
            $this->em->persist($profile);
            $this->em->flush();

            $generalInfoBox = $this->render('TheaterjobsUserBundle:AccountSettings:generalInfo.html.twig', array(
                'profile' => $profile,
                'billingAddress' => $billingAddress
            ));
            return new JsonResponse ([
                'error' => false,
                'requestedNameChange' => $requestedNameChange,
                'requestedNameMessage' => $requestedNameMessage,
                'data' => $generalInfoBox->getContent()
            ]);
        } else {
            $errors = $this->getErrorMessagesAJAX($form);
        }
        return new JsonResponse(['errors' => $errors]);
    }


    /**
     * @Route("/delete/{slug}", name="tj_user_account_settings_delete", defaults={"slug" = null} , condition="request.isXmlHttpRequest()")
     * @Method({"POST"})
     * @param Request $request
     * @param Profile|null $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param $slug
     */
    public function deleteAction(Request $request, Profile $profile = null)
    {

        $getProfile = $this->isSetProfile($profile);
        $user = $getProfile->getUser();
        if ($user->getDisabledDeleteAccount()) {
            $msg = $this->getTranslator()->trans("flash.error.this_account_is_not_deletable", [], 'flashes');
            $this->addFlash('accountSettings', ['danger' => $msg]);
            return $this->redirect($this->generateUrl('tj_user_account_settings', ['slug' => $getProfile->getSlug()]));
        }

        $form = $this->createCreateForm('tj_user_profile_delete_passCheck', $user, $options = [], 'tj_user_account_settings_delete');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $pass = $request->request->get('tj_user_profile_delete_passCheck')['password'];
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $encodedPass = $encoder->encodePassword($pass, $user->getSalt());

            $userExist = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array("password" => $encodedPass));

            $errorBag = [];
            if ((count($userExist) == 0)) {
                array_push($errorBag, ['field' => '#tj_user_profile_delete_passCheck_password', 'message' => 'The current password you entered is not correct.']);
            }

            if (count($errorBag) > 0) {
                return new JsonResponse ([
                    'error' => true,
                    'errors' => $errorBag
                ]);
            }

            //array_push($errorBag,['field'=>'#tj_user_profile_delete_passCheck_password','message'=>'Password OK.Proceed with delete.']);

            return new JsonResponse ([
                'error' => true,
                'errors' => $errorBag
            ]);

            $this->replaceDataForDeletion($getProfile);
            $this->em->persist($user);
            $this->em->persist($getProfile);
            $this->em->flush();
            $dispatcher = $this->get('event_dispatcher');
            $uacEvent = new UserActivityEvent($user, $this->get('translator')->trans('tj.user.activity.deactivated.account', array(), 'activity'));
            $dispatcher->dispatch("UserActivityEvent", $uacEvent);

            if ($getProfile->getSlug() === $this->getUser()->getProfile()->getSlug()) {
                return $this->redirect($this->generateUrl('fos_user_security_logout'));
            } else {
                return $this->redirect($this->generateUrl('tj_user_account_settings', array("slug" => $getProfile->getSlug())));
            }

        } else {
            $errors = $this->getErrorMessages($form);
        }

        return $this->render('TheaterjobsUserBundle:AccountSettings/Modal:delete.html.twig', array(
                'entity' => $getProfile,
                'form' => $form->createView(),
                'errors' => $errors,
            )
        );


    }

    /**
     *  Return profile of user or admin specified profile user
     *
     * @param Profile|null $profile
     * @return Profile
     */
    private function isSetProfile(Profile $profile = null)
    {
        if (!$profile) {
            return $this->getProfile();
        }

        if ((!$this->isGranted('ROLE_ADMIN')) && ($profile->getSlug() !== $this->getProfile()->getSlug())) {
            throw new AccessDeniedException();
        }

        return $profile;
    }

    /**
     * Revoke membership for user if it has one
     *
     * @Route(
     *     "/revoke-membership/{slug}",
     *     name="tj_user_account_settings_revoke_membership",
     *     condition="request.isXmlHttpRequest()",
     *     options={"expose"=true}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Method({"GET"})
     * @param Profile $profile
     * @return JsonResponse |\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function revokeMembershipAction(Profile $profile)
    {
        $user = $profile->getUser();
        $dispatcher = $this->get('event_dispatcher');

        if (!$user->getMembershipExpiresAt()) {
            return new JsonResponse([
                'error' => true,
                'message' => $this->getTranslator()->trans('accountSettings.revokeMembership.noMembership')
            ], 200);
        }
        $this->archiveEducations($user);
        $event = new MembershipExpiredEvent($user);
        $event->setQueue("app");
        $event->setFlush(false);
        try {
            $dispatcher->dispatch(MembershipEvents::MEMBERSHIP_EXPIRED, $event);
        } catch (OptimisticLockException $e) {
            return new JsonResponse([
                'error' => true,
                'message' => "Couldn't revoke membership.",
                'membershipBlock' => $this->accountSettings->getMembershipBlock($profile)
            ], 200);
        }
        //Remove all pending status billings
        $this->removePendingBills($profile);
        if ($profile->getIsPublished()) {
            //Un-publish profile
            $this->forward('TheaterjobsProfileBundle:Profile:profilePublish', [
                'slug' => $profile->getSlug(),
                'publish' => 0
            ]);
        }
        $this->revokeNotifications($user);
        $this->em->flush();

        $result = [
            'error' => false,
            'message' => $this->getTranslator()->trans(
                "flash.success.membership.revoked", [], 'flashes'
            ),
            'membershipBlock' => $this->accountSettings->getMembershipBlock($profile)
        ];

        return new JsonResponse($result, 200);

    }

    /**
     * Remove pending bills when revoking membership
     * @param Profile profile
     */
    private function removePendingBills(Profile $profile)
    {
        $bookings = $profile->getBookings();
        $status = $this->em->getRepository(BillingStatus::class)->findOneBy(['name' => BillingStatus::STORNO]);

        foreach ($bookings as $booking) {
            $billings = $booking->getBillings();
            if ($billings) {
                foreach ($billings as $billing) {
                    $billing->setBillingStatus($status);
                }
            }
        }
    }

    /**
     * Send notification for revoking memmbership,
     * log user activity,
     * remove expiration notification
     * @param $user
     */
    private function revokeNotifications($user)
    {

        //Send notification to user
        $title = 'dashboard.notification.membership.revoked';
        $description = 'dashboard.notification.membership.revoked.description';
        $link = 'tj_main_default_contact_site';

        $notification = new Notification();
        $notification->setTitle($title)
            ->setDescription($description)
            ->setCreatedAt(Carbon::now())
            ->setRequireAction(false)
            ->setLink($link);

        $event = (new NotificationEvent())
            ->setObjectClass(User::class)
            ->setObjectId($user->getId())
            ->setNotification($notification)
            ->setFrom($user)
            ->setUsers($user)
            ->setType('membership_revoked')
            ->setFlush(false);

        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch('notification', $event);

        $uacEvent = new UserActivityEvent(
            $user,
            $this->get('translator')->trans('tj.user.activity.revoked.membership',
                array(),
                'activity'
            ),
            false,
            null,
            null,
            false
        );
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        $markNotificationReadEvent = new MarkNotificationAsReadEvent(
            $user,
            'membership_about_expire',
            $user,
            null,
            false
        );

        $dispatcher->dispatch("MarkNotificationAsReadEvent", $markNotificationReadEvent);
    }

    /**
     *
     * @Route("/membershipexpire/{slug}", name="tj_user_account_settings_membership_expire", defaults={"slug" = null})
     * @Method({"GET", "POST","PUT"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function membershpipExpireDateAction(Request $request, Profile $profile = null)
    {

        $getProfile = $this->isSetProfile($profile);

        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($getProfile->getUser(), $this->get('translator')->trans('tj.user.activity.changed.membership.expire.date.for', array(), 'activity'));
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);

        $getProfile->getUser()->setMembershipExpiresAt(Carbon::createFromFormat('d.m.Y', $request->request->get('membershipExpire')));
        $this->getEM()->persist($getProfile);
        $this->getEM()->flush();

        $msg = $this->getTranslator()->trans("flash.success.changed.membership.expire.date", [], 'flashes');
        $this->addFlash('accountSettings', ['success' => $msg]);

        if ($getProfile->getSlug() === $this->getUser()->getProfile()->getSlug()) {
            return $this->redirect($this->generateUrl('tj_user_account_settings'));
        } else {
            return $this->redirect($this->generateUrl('tj_user_account_settings', array("slug" => $getProfile->getSlug(), 'tab' => 'billing')));
        }
    }

    /**
     *
     * @Route("/quit/contract/{slug}", name="tj_user_account_settings_quit_contract",  defaults={"slug" = null}, options={"expose"=true})
     * @Method({"POST"})
     * @param Profile $profile
     * @return JsonResponse
     */
    public function quitContractAction(Profile $profile = null)
    {
        try {
            $profile = $this->isSetProfile($profile);
            $user = $profile->getUser();

            if (!$user->hasRole('ROLE_MEMBER')) {
                // do not do anything with a user thats admin or simple user
                // return $this->redirect($this->generateUrl('tj_user_account_settings'));
                return new JsonResponse(['message' => 'No Membership Found']);
            }

            $profile = $user->getProfile();
            $paymentMethod = $this->getEM()->getRepository("TheaterjobsMembershipBundle:Paymentmethod")->paymentMethodByProfile($profile);
            /** @var Booking $billing */
            $billingStatus = $profile->getLastBooking()->getLastBilling()->getBillingStatus();

            if ($billingStatus->isPending() || $billingStatus->isOpen()) {
                return new JsonResponse(['message' => $this->getTranslator()->trans('flash.error.membership.pending.exists')]);
            }

            if ($paymentMethod->getShort() != 'direct') {
                return new JsonResponse(['message' => 'No direct debit method']);
            }

            $end = Carbon::createFromFormat('Y-m-d H:i:s', $user->getMembershipExpiresAt()->format('Y-m-d H:i:s'))->subDay(42);
            $now = Carbon::today();
            if ($now->lte($end)) {
                $user->setRecuringPayment(false);
                $user->setQuitContract(true);
                $user->setQuitContractDate(Carbon::now());


                $notification = new Notification();
                $title = 'tj.notification.contract.quited.success';
                $notification
                    ->setTitle($title)
                    ->setCreatedAt(new \DateTime())
                    ->setDescription('')
                    ->setRequireAction(false)
                    ->setLink('tj_user_account_settings')
                    ->setLinkKeys(array('tab' => 'billing'));

                $notificationEvent = (new NotificationEvent())
                    ->setObjectClass(User::class)
                    ->setObjectId($user->getId())
                    ->setNotification($notification)
                    ->setUsers($user)
                    ->setType('order_received');

                $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);

                $msg = $this->getTranslator()->trans("flash.success.contract.quit.success", [], 'flashes');
                $this->addFlash('accountSettings', ['success' => $msg]);
            } else {
                $user->setHasRequiredRecuringPaymentCancel(true);
                $user->setQuitContract(true);
                $user->setQuitContractDate(Carbon::now());
                $membershipExpires = Carbon::instance($user->getMembershipExpiresAt());
                $user->setMembershipExpiresAt($membershipExpires->addYear());

                $msg = $this->getTranslator()->trans("flash.error.contract.quitted.ends_on %date%", ['%date%' => $membershipExpires->format('d.m.Y')], 'flashes');
                $this->addFlash('accountSettings', ['warning' => $msg]);
            }

            $this->getEM()->persist($user);
            $this->getEM()->flush();

            return new JsonResponse([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()]);

        }
    }

    /**
     * Prevents user to delete his account
     *
     * @Route("/blockdeletion/{slug}",
     *     name="tj_user_block_account_delete",
     *     defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"expose"=true})
     *
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function blockDeletionAction(Profile $profile)
    {
        $user = $profile->getUser();
        $user->setDisabledDeleteAccount(true);
        $this->getEM()->persist($user);
        $this->getEM()->flush();

        $uacEvent = new UserActivityEvent($profile->getUser(), $this->get('translator')->trans('user.activity.admin.blocked.accountDeletion', array(), 'activity'));
        $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

        $result = [
            'error' => false,
            'message' => $this->getTranslator()
                ->trans('flash.success.this_account_is_not_deletable', array(), 'flashes')
        ];
        return new JsonResponse($result);
    }

    /**
     * Removes the prevention from deleting the profile
     *
     * @Route("/unblockdeletion/{slug}",
     *     name="tj_user_unblock_account_delete",
     *     defaults={"slug" = null},
     *     condition="request.isXmlHttpRequest()",
     *     options={"expose"=true})
     *
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function unBlockDeletionAction(Profile $profile)
    {
        $user = $profile->getUser();
        $user->setDisabledDeleteAccount(false);
        $this->getEM()->persist($user);
        $this->getEM()->flush();

        $uacEvent = new UserActivityEvent($profile->getUser(), $this->get('translator')->trans('user.activity.admin.unblocked.accountDeletion', array(), 'activity'));
        $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

        $result = [
            'error' => false,
            'message' => $this->getTranslator()
                ->trans('flash.success.this_account_is_deletable', array(), 'flashes')
        ];
        return new JsonResponse($result);
    }

    /**
     *
     * @Route("/startPayment/{slug}", name="tj_user_start_payment")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function startUserPaymentAction(Profile $profile)
    {
        $this->session->set('pay_profile', $profile);
        return $this->redirect($this->generateUrl('tj_membership_index'));
    }

    private function replaceDataForDeletion(Profile $profile)
    {

        $audios = $profile->getMediaAudio();
        if (count($audios) > 0) {
            foreach ($audios as $audio) {
                $profile->removeMediaAudio($audio);
                unlink($audio->getAbsolutePath());
                $full_path = $this->getFullPath($audio->getSubdir(), $audio->getPath());
                (file_exists($full_path)) ? unlink($full_path) : $full_path = NULL;

            }
        }

        $images = $profile->getMediaImage();
        if (count($images) > 0) {
            foreach ($images as $image) {
                $profile->removeMediaImage($image);
                $sizedFilename = explode('.', $image->getPath());
                $sizedFilename[0] = $sizedFilename[0] . '@2x';
                $sizedFilename = implode(".", $sizedFilename);
                $full_path = $this->getFullPath($image->getSubdir(), $image->getPath());
                $full_path_sized = $this->getFullPath($image->getSubdir(), $sizedFilename);
                (file_exists($full_path)) ? unlink($full_path) : $full_path = NULL;
                (file_exists($full_path_sized)) ? unlink($full_path_sized) : $full_path_sized = NULL;
            }
        }

        $pdfs = $profile->getMediaPdf();
        if (count($pdfs) > 0) {
            $pdfs = $profile->getMediaPdf();
            foreach ($pdfs as $pdf) {
                $profile->removeMediaPdf($pdf);
                $full_path = $this->getFullPath($pdf->getSubdir(), $pdf->getPath());
                (file_exists($full_path)) ? unlink($full_path) : $full_path = NULL;
            }
        }
        $videos = $profile->getVideos();
        if (count($videos) > 0) {
            $videos = $profile->getVideos();
            foreach ($videos as $video) {
                $profile->removeVideo($video);
            }
        }

        if ($profile->getContactSection()) {
            $this->em->remove($profile->getContactSection());
        }

        if ($profile->getBiographySection()) {
            $this->em->remove($profile->getBiographySection());
        }

        if ($profile->getSkillSection()) {
            $this->em->remove($profile->getSkillSection());
        }

        if ($profile->getQualificationSection()) {
            $this->em->remove($profile->getQualificationSection());
        }

        $profile->setContactSection(null);
        $profile->setBiographySection(null);
        $profile->setSkillSection(null);
        $profile->setQualificationSection(null);

        $profile->setIsPublished(false);
        $profile->setProfileName('DELETED_ACCOUNT' . $profile->getId());
        $profile->setSubtitle2('DELETED_ACCOUNT');
        $profile->setProfileActualityText('');
        $profile->setSlug('DELETED_ACCOUNT' . $profile->getId());

        $user = $profile->getUser();
        $jobFavorites = $this->em->getConnection()->executeQuery('SELECT * FROM tj_inserate_inserates_favourites WHERE user_id = ?', array($user->getId()))->fetchAll();
        foreach ($jobFavorites as $jf) {
            $this->em->getConnection()->executeQuery("DELETE FROM tj_inserate_inserates_favourites WHERE inserate_id = ? AND user_id = ?", array($jf['inserate_id'], $jf['user_id']));
        }

        $profileFavorites = $this->em->getConnection()->executeQuery("SELECT * FROM tj_profile_profiles_favourites WHERE profile_from_id = ?", array($profile->getId()))->fetchAll();
        foreach ($profileFavorites as $pf) {
            $this->em->getConnection()->executeQuery("DELETE FROM tj_profile_profiles_favourites WHERE profile_from_id = ? AND profile_to_id = ?", array($pf['profile_from_id'], $pf['profile_to_id']));
        }

        $teams = $this->em->getRepository("TheaterjobsUserBundle:UserOrganization")->findBy(array('user' => $user));
        $networks = $this->em->getRepository("TheaterjobsInserateBundle:Network")->findBy(array('user' => $user));
        $educations = $this->em->getRepository("TheaterjobsInserateBundle:Education")->findBy(array('user' => $user));
        $jobmails = $this->em->getRepository("TheaterjobsInserateBundle:JobmailQuery")->findBy(array('user' => $user));

        foreach ($networks as $network) {
            $this->em->remove($network);
        }

        foreach ($educations as $education) {
            $this->em->remove($education);
        }

        foreach ($jobmails as $jobmail) {
            $this->em->remove($jobmail);
        }

        foreach ($teams as $team) {
            $this->em->remove($team);
        }

        $mailer = $this->get('base_mailer');
        $mailer->sendEmailMessage(
            $this->getTranslator()->trans(
                'tj.email.text.account.deleted.success.title', array(), 'emails'
            ),
            $this->getTranslator()->trans(
                'tj.email.text.account.deleted.success.body', array(), 'emails'
            ),
            'info@theaterjobs.de',
            $user->getEmail(),
            'text/html',
            'info@theaterjobs.de'
        );

        $user->setEmail('DELETED_ACCOUNT' . $user->getId() . '@example.com');
        $user->setEmailCanonical($user->getEmail());
        $user->setUsername($user->getEmail());
        $user->setUsernameCanonical($user->getEmail());
        $user->setEnabled(false);
        $user->setMembershipExpiresAt(null);
        $this->em->persist($user);
        $this->em->persist($profile);
        $this->em->flush();

    }

    /**
     * @Route("/not_logged_in_feature/{slug}", name="not_logged_in_feature")
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function notLoggedinFeautreAction($slug)
    {
        return $this->redirect($this->generateUrl('tj_profile_profile_show', array(
            "slug" => $slug
        )));
    }

    /**
     * Send/unsend a notification to user that the email is not valid anymore
     *
     * @Route(
     *     "/validateEmail/{slug}/{action}",
     *     name="tj_user_email_validate",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     requirements={"action": "\d+"}
     *     )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @param Profile $profile
     * @param integer $action
     *
     * @return JsonResponse
     */
    public function emailValidNotification(Profile $profile, $action)
    {

        //Undo notification
        if ($action == 0) {

            $profile->getProfileAllowedTo()->setEmailWarning(false);
            $this->em->persist($profile);
            $this->em->flush();

            $exists = $this->checkNotification($profile->getUser(), 'renew_email');
            $translator = $this->getTranslator();

            //Check if there exists a notification to be deleted
            if ($exists) {
                $event = new MarkNotificationAsReadEvent(
                    $profile->getUser(),
                    'renew_email',
                    $profile->getUser()
                );

                $this->get('event_dispatcher')->dispatch('MarkNotificationAsReadEvent', $event);

                $result = array(
                    'error' => false,
                    'message' => $translator->trans('flash.success.notification.deleted'),
                );
                $uacEvent = new UserActivityEvent($profile->getUser(), $this->get('translator')->trans('user.activity.admin.unchecked.emailValidationFlag', array(), 'activity'));
                $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

            } else {
                $result = array(
                    'error' => true,
                    'message' => $translator->trans('flash.error.notification.notFound'),
                );
            }
        } //Send notification
        else if ($action == 1) {

            $profile->getProfileAllowedTo()->setEmailWarning(true);
            $this->em->persist($profile);
            $this->em->flush();

            $exists = $this->checkNotification($profile->getUser(), 'renew_email');
            $translator = $this->getTranslator();

            //Check if there is a notification for this case
            if (!$exists) {

                $link = 'tj_user_account_settings';
                $linkParams = array(
                    'slug' => $profile->getSlug()
                );

                $notification = new Notification();

                $notification->setTitle('dashboard.notification.user.email.renew')
                    ->setDescription('dashboard.notification.user.email.renew.description')
                    ->setCreatedAt(Carbon::now())
                    ->setRequireAction(true)
                    ->setLink($link)
                    ->setLinkKeys($linkParams);

                $user = $profile->getUser();
                $event = (new NotificationEvent())
                    ->setObjectClass(User::class)
                    ->setObjectId($user->getId())
                    ->setNotification($notification)
                    ->setFrom($user)
                    ->setUsers($user)
                    ->setType('renew_email');

                $this->get('event_dispatcher')->dispatch('notification', $event);

                $result = array(
                    'error' => false,
                    'message' => $translator->trans('flash.success.notification.sent'),
                );

            } else {
                $result = array(
                    'error' => true,
                    'message' => $translator->trans('flash.error.notification.is.already.sent'),
                );
            }
            $uacEvent = new UserActivityEvent($profile->getUser(), $this->get('translator')->trans('user.activity.admin.checked.emailValidationFlag', array(), 'activity'));
            $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

        } else {
            $result = [
                'error' => true,
                'message' => 'Invalid parameters'
            ];
            return new JsonResponse($result, 400);
        }


        return new JsonResponse($result);
    }

    /**
     *
     * @Route("/all/logs/{slug}", name="tj_user_account_settings_all_logs" , condition="request.isXmlHttpRequest()")
     * @Method({"GET", "PUT"})
     *
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showLogsAction(Request $request, Profile $profile)
    {

        $activities = $this->getRepository('TheaterjobsUserBundle:UserActivity')
            ->findBy(
                array(
                    'user' => $profile->getUser()
                ),
                array(
                    'id' => 'desc'
                )
            );

        $template = 'TheaterjobsUserBundle:AccountSettings\Modal:logsAll.html.twig';

        return $this->render($template, [
                'entity' => $profile,
                'activities' => $activities
            ]
        );

    }

    /**
     * Creates a form to add comment on AccountSettings of a User.
     *
     * @param Profile $profile
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCommentForm($profile)
    {
        $entity = new AdminUserComments();

        $form = $this->createForm(
            'theaterjobs_userbundle_admin_user_comments',
            $entity,
            array(
                'action' => $this->generateUrl(
                    'organization_admin_user_comments_create',
                    array('slug' => $profile->getSlug())
                ),
                'method' => 'POST',
            )
        );

        $form->add('submit', 'submit', array('label' => $this->getTranslator()->trans('button.comment')));

        return $form;
    }


    function getFullPath($subDir, $filename)
    {
        $appPath = $this->container->getParameter('kernel.root_dir');
        $appPath = explode('/', $appPath);
        array_pop($appPath);
        $appPath = implode("/", $appPath);
        return $appPath . '/web/uploads/profile/' . $subDir . '/' . $filename;
    }

    /**
     * Get Admin accounts
     * @param Profile $profile
     * @return array
     */
    public function adminAccountSettings($profile)
    {
        $user = $profile->getUser();
        $last10DaysLogins = $this->em->getRepository(User::class)->getLastTenDaysAuthentications($user->getId());
        $hasMembership = $user->getMembershipExpiresAt() ?: false;

        $billing = $profile->getLastBilling();
        if ($billing) {
            $invoice['id'] = $billing->getId();
            $invoice['statusId'] = $billing->getBillingStatus()->getId();
            //Get all invoice statuses
            $status = $this->em->getRepository('TheaterjobsMembershipBundle:BillingStatus')->findAll();
            $invoice['status'] = $status;
        }
        //Check if user has his email blocked
        $blockedEmail = $this->em->getRepository('TheaterjobsUserBundle:EmailBlacklist')->findOneBy(['email' => $user->getEmail()]);
        //Check if user has his debit card blocked
        $hasBlockDebitCard = $profile->getBlockedPaymentmethods()->filter(function ($e) {
            return $e->isDebit();
        });
        //Check if user has his paypal blocked
        $hasBlockPaypal = $profile->getBlockedPaymentmethods()->filter(function ($e) {
            return $e->isPaypal();
        });
        //Create AdminUserComments form
        $comments['commentsForm'] = $this->createCommentForm($profile)->createView();
        $comments['commentsEntities'] = $this->em->getRepository('TheaterjobsUserBundle:AdminUserComments')
            ->findBy(['user' => $profile->getUser()], ['publishedAt' => 'DESC']);
        $options = [
            'last10DaysLogins' => $last10DaysLogins,
            'hasMembership' => $hasMembership,
            'hasEmailWarning' => $profile->getProfileAllowedTo()->getEmailWarning(),
            'hasBlockDebitCard' => !$hasBlockDebitCard->isEmpty(),
            'hasBlockPaypal' => !$hasBlockPaypal->isEmpty(),
            'hasBlockAccountDeletion' => $user->getDisabledDeleteAccount(),
            'hasBlockEmail' => $blockedEmail,
            'invoice' => $billing ? $invoice : null,
            'comments' => $comments
        ];
        $options = array_merge($options, $this->accountSettings->getActivityLogs($profile));
        return $options;
    }

    /**
     * Archive all published education of the user that just ends the membership
     *
     * @param User $user
     */
    public function archiveEducations(User $user)
    {
        //Get all published educations of this user
        $finder = $this->container->get('fos_elastica.finder.theaterjobs.job');
        $query = $this->container->get('fos_elastica.manager')->getRepository(Job::class)->getPublishedEducationsByUser($user->getId());
        $publishedEducations = $finder->find($query);
        $batchSize = 20;
        $i = 1;

        foreach ($publishedEducations as $education) {
            //Change the status to archived
            $education->setStatus(Education::STATUS_ARCHIVED);
            if (($i % $batchSize) === 0) {
                $this->em->flush(); // Executes all updates.
                $this->em->clear(); // Detaches all objects from Doctrine!
            }
            ++$i;
        }

        $this->em->flush();
    }
}
