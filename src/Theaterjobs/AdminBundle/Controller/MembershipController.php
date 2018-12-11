<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\UserBundle\Services\AccountSettings;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * The Membership Controller.
 *
 * Provides the Overview of the Memberships available
 *
 * @category Controller
 * @Route("/membership")
 */
class MembershipController extends BaseController
{
    use ESUserActivity;

    /**
     * @DI\Inject("theaterjobs_user_bundle.account_settings")
     * @var AccountSettings
     */
    public $accountSettings;

    /**
     * Block specified payment method
     * @Route(
     *     "/block/{slug}/{payment_id}",
     *     name="tj_membership_admin_paymentmethod_block",
     *     options={"expose"=true},
     *     requirements={"payment_id": "\d+"},
     *     condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter("payment", options={"mapping": {"payment_id": "id"}})
     * @param Profile $profile
     * @param Paymentmethod $payment
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function blockPaymentMethodAction(Profile $profile, Paymentmethod $payment)
    {
        if (!$profile->getBlockedPaymentmethods()->contains($payment)) {

            $profile->addBlockedPaymentmethod($payment);
            $this->getEM()->persist($profile);
            $this->getEM()->flush();

            $uacEvent = new UserActivityEvent($profile->getUser(), $this->getTranslator()->trans('user.activity.admin.blocked.paymentMethod %$payment->getShort()%', array(), 'activity'));
            $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

            $result = [
                'error' => false,
                'message' => $this->getTranslator()->trans(
                    "flash.success.payment_blocked", [], 'flashes'
                )
            ];
        } else {
            $result = [
                'error' => true,
                'message' => $this->getTranslator()->trans(
                    'flash.error.payment_already_blocked', [], 'flashes'
                )
            ];
        }
        return new JsonResponse($result);
    }

    /**
     * Unblock specified payment method
     * @Route(
     *     "/unblock/{slug}/{payment_id}",
     *     name="tj_membership_admin_paymentmethod_unblock",
     *     options={"expose"=true},
     *     requirements={"payment_id": "\d+"},
     *     condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter("payment", options={"mapping": {"payment_id": "id"}})
     * @param Profile $profile
     * @param Paymentmethod $payment
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function unBlockPaymentMethodAction(Profile $profile, Paymentmethod $payment)
    {
        if ($profile->getBlockedPaymentmethods()->contains($payment)) {

            $profile->removeBlockedPaymentmethod($payment);
            $payment->removeBlockedForProfile($profile);
            $this->getEM()->persist($profile);
            $this->getEM()->persist($payment);
            $this->getEM()->flush();

            $uacEvent = new UserActivityEvent($profile->getUser(), $this->getTranslator()->trans('user.activity.admin.unblocked.paymentMethod %$payment->getShort()%', [], 'activity'));
            $this->get('event_dispatcher')->dispatch("UserActivityEvent", $uacEvent);

            $result = [
                'error' => false,
                'message' => $this->getTranslator()->trans(
                    "flash.success.payment_unblocked", [], 'flashes'
                )
            ];
        } else {
            $result = [
                'error' => true,
                'message' => $this->getTranslator()->trans(
                    'flash.error.payment_blocked_doesnt_exists', [], 'flashes'
                )
            ];
        }
        return new JsonResponse($result);
    }

    /**
     * Change status of an Invoice/Billing
     *
     * @Route(
     *     "/invoiceStatus/{id}/{action}",
     *     name="tj_admin_membership_invoice_status",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter("status", options={"mapping": {"action": "id"}})
     * @param Billing $billing
     * @param BillingStatus $status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function InvoiceStatus(Billing $billing, BillingStatus $status)
    {
        if (!$status) {
            return new JsonResponse([
                'error' => true,
                'message' => $this->getTranslator()->trans('flash.error.invoiceStatus.invalid', [], 'flashes')
            ]);
        }
        $billing->setBillingStatus($status);

        $this->getEM()->persist($billing);
        $this->getEM()->flush();

        return new JsonResponse([
            'error' => false,
            'message' => $this->getTranslator()->trans('flash.success.invoiceStatus.changed')
        ]);
    }

    /**
     * Unblock specified payment method
     * @Route(
     *     "/change-paid-until/{slug}",
     *     name="tj_admin_membership_change_paid_until",
     *     condition="request.isXmlHttpRequest()",
     *     options={"expose"=true}
     * )
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changePaidUntil(Request $request, Profile $profile)
    {
        $dateStr = $request->request->get('date');

        if (!empty($dateStr) && Carbon::createFromFormat('Y.m.d', $dateStr) !== false) {
            $user = $profile->getUser();
            $dateTime = \DateTime::createFromFormat('Y.m.d', $dateStr);
            $user->setMembershipExpiresAt($dateTime);

            $this->logUserActivity(
                $user,
                $this->getTranslator()->trans('tj.admin.activity.changed.paidUntil %date%',
                    ['%date%' => $dateTime->format('d.m.Y')],
                    'activity')
            );
            $this->getEM()->persist($user);
            $this->getEM()->flush();

            $result = [
                'error' => false,
                'message' => $this->getTranslator()->trans(
                    'flash.success.paidUntil.changed'),
                'membershipBlock' => $this->accountSettings->getMembershipBlock($profile),
                'activityBlock' => $this->accountSettings->getActivityBlock($profile)
            ];

        } else {
            $result = [
                'error' => true,
                'message' => $this->getTranslator()->trans(
                    'flash.error.paidUntil.format.error')
            ];
        }
        return new JsonResponse($result);
    }
}
