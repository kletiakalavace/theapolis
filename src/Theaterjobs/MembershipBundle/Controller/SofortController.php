<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sofort\SofortLib\Sofortueberweisung;
use Theaterjobs\MainBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Event\OrderEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * Class SofortController
 * @package Theaterjobs\MembershipBundle\Controller
 * @Route("/sofort")
 */
class SofortController extends BaseController
{
    /** @DI\Inject("event_dispatcher") */
    private $dispatcher;

    /** @DI\Inject("theaterjobs_membership.mailer") */
    public $mailer;

    /** @DI\Inject("translator") */
    private $translator;

    /** @DI\Inject("theaterjobs_membership.sepa") */
    public $sepa;

    /**
     * Route forwarded from BookingController
     * @Route("/make-payment/{id}", name="tj_membership_sofort_make_payment")
     * @param Request $request
     * @param Billing $billing
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ErrorException*
     * @Security("is_granted('make_payment_using_sofort', billing)")
     */
    public function makePaymentUsingSofortAction(Request $request, Billing $billing)
    {
        $debitAccount = $request->request->get('theaterjobs_membership_booking_type')['debitAccount'];
        $generatedBIC = $this->sepa->generateBic($debitAccount['iban']);
        if (!$generatedBIC) {
            $this->addFlash('dashboard', ['error' => $this->translator->trans("flash.error.membership.bic.notGenerated")]);
            return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
        }
        $total = floatval($billing->getSumNet()) + floatval($billing->getSumVat());
        //country data has to be come from billing address
        $countryData = $this->getProfile()->getBillingAddress()->getCountry();
        // Payment Transaction
        $Sofortueberweisung = new Sofortueberweisung($this->getParameter('sofort_config_key'));
        $Sofortueberweisung->setAmount($total);
        $Sofortueberweisung->setCurrencyCode('EUR');
        $Sofortueberweisung->setSenderCountryCode($countryData);
        $Sofortueberweisung->setCustomerprotection(true);
        $Sofortueberweisung->setEmailCustomer($this->getUser()->getEmail());
        $Sofortueberweisung->setReason($this->translator->trans('sofort.payment.reason', [], 'messages'), $billing->getNumber());
        // Success Url
        $Sofortueberweisung->setSuccessUrl($this->generateUrl('tj_membership_sofort_execute_payment', ['id' => $billing->getId()], true), true);
        // Cancel Url
        $Sofortueberweisung->setAbortUrl($this->generateUrl('tj_membership_sofort_cancel_payment', ['id' => $billing->getId()], true));
        $Sofortueberweisung->enableLogging = true;
        $Sofortueberweisung->sendRequest();

        if ($Sofortueberweisung->isError()) {
            $errors = $Sofortueberweisung->getErrors();
            if ($errors[0]['code'] == 8023) {
                $msg = $this->translator->trans('flash.error.payment.invalid.bic', [], 'flashes');
                $this->addFlash('membershipNew', ['danger' => $msg]);
                return $this->redirect($this->generateUrl("tj_membership_booking_new"));
            } else {
                //SOFORT-API didn't accept the data
                throw new \ErrorException("An error occurred, please try latter");
            }
        } else {
            //buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
            $paymentUrl = $Sofortueberweisung->getPaymentUrl();
            // @TODO Change var based on env
            return $this->redirect($paymentUrl . '?var=testing');
        }
    }

    /**
     * Show sepamandate
     *
     * @Route("/execute-payment/{id}", name="tj_membership_sofort_execute_payment")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePaymentAction(Billing $billing)
    {
        if (!$this->isGranted('execute_payment_using_sofort', $billing)) {
            throw $this->createNotFoundException();
        }
        $this->getEM()->getConnection()->beginTransaction();
        try {
            $event = new OrderEvent($billing);
            $this->dispatcher->dispatch(MembershipEvents::MEMBERSHIP_ORDER, $event);
            //Delete NRA notifications
            $this->readNotification($this->getUser(), 'become_member', $this->getUser());
            $this->readNotification($this->getUser(), 'membership_about_expire', $this->getUser());
            $completed = $this->getEM()->getRepository(BillingStatus::class)->findOneByName(BillingStatus::COMPLETE);
            // Set Billing Status
            $billing->setBillingStatus($completed);
            // Send Notification/email
            $this->mailer->sendBillingEmailMessage($billing);
            $this->sendNotifications($billing, $this->getProfile());
            $this->getEM()->flush();
            $this->addFlash('dashboard', ['success' => $this->translator->trans("flash.success.membership.complete.thanks")]);
            $this->getEM()->getConnection()->commit();
        } catch (\Exception $err) {
            $this->getEM()->getConnection()->rollback();
            $this->addFlash('dashboard', ['error' => $this->translator->trans("flash.message.error.membership.problem")]);
        }
        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * cancel payment, remove the billing row form the database
     *
     * @Route("/cancel-payment/{id}", name="tj_membership_sofort_cancel_payment")
     * @param Billing $billing
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function paymentCancelAction(Billing $billing)
    {
        if (!$this->isGranted('execute_payment_using_sofort', $billing)) {
            throw $this->createNotFoundException();
        }
        $em = $this->getEM();
        $booking = $billing->getBooking();
        $em->remove($booking);
        $em->remove($billing);
        $em->flush();
        $this->addFlash('dashboard', ['warning' => $this->translator->trans("flash.warning.membership.canceled.payment")]);
        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * Sends notification after payment
     * Welcome notification if its the first time, or diff one next times
     *
     * @param Billing $billing
     * @param $profile
     */
    private function sendNotifications($billing, $profile)
    {
        // we must check is booking greater then 1, because on this point booking already exists (Jana)
        $countBookings = $this->getEM()->getRepository(Booking::class)->countBooking($profile);
        // Since one booking is already saved on db
        $renew = $countBookings > 1 ? ".renew" : '';

        $notification = new Notification();
        $title = "tj.notification$renew.membership";

        $notification
            ->setTitle($title)
            ->setCreatedAt(new \DateTime())
            ->setDescription('')
            ->setRequireAction(false)
            ->setLink('tj_user_account_settings')
            ->setLinkKeys(['tab' => 'billing']);

        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(Billing::class)
            ->setObjectId($billing->getId())
            ->setNotification($notification)
            ->setUsers($this->getUser())
            ->setType('order_received')
            ->setFlush(false);

        $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);

    }
}