<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\MainBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\MainBundle\Utility\Traits\ReadNotificationTrait;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Event\OrderEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\MembershipEvents;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\FlowConfig;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Event\NotificationEvent;


/**
 * PayPalController
 *
 * @category Controller
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @Route("/paypal")
 */
class PayPalController extends BaseController
{
    use ReadNotificationTrait;

    /** @DI\Inject("event_dispatcher") */
    private $dispatcher;

    /** @DI\Inject("theaterjobs_membership.mailer") */
    public $mailer;

    /**
     * @Route("/make-payment/{id}", name="tj_membership_paypal_make_payment")
     * @param Billing $billing
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("is_granted('make_payment_using_paypal', billing)")
     */
    public function makePaymentUsingPayPalAction(Billing $billing)
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        // Membership Item
        $item1 = new Item();
        $item1->setName($billing->getBooking()->getMembership()->getTitle())
            ->setCurrency('EUR')
            ->setQuantity(1)
            ->setPrice($billing->getSumNet());
        // Vat Item
        $item2 = new Item();
        $item2->setName($this->getTranslator()->trans('paypal.title.vat', [], 'messages'))
            ->setCurrency('EUR')
            ->setQuantity(1)
            ->setPrice($billing->getSumVat());
        // Paypal Fee Item
        $item3 = new Item();
        $item3->setName($this->getTranslator()->trans('paypal.title.fee', [], 'messages'))
            ->setCurrency('EUR')
            ->setQuantity(1)
            ->setPrice($billing->getPaymentmethodPrice());
        $itemList = new ItemList();
        $itemList->setItems([$item1, $item2, $item3]);
        $total = floatval($billing->getSumNet()) + floatval($billing->getSumVat()) + floatval($billing->getPaymentmethodPrice());
        // Set Total Amount
        $amount = new Amount();
        $amount->setCurrency('EUR');
        $amount->setTotal($total);
        // Transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription($this->getTranslator()->trans('tj.membership.paypal.bill.description', [], 'flashes'));
        $transaction->setItemList($itemList);

        // Set red urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->generateUrl('tj_membership_execute_paypal', ['id' => $billing->getId()], true));
        $redirectUrls->setCancelUrl($this->generateUrl('tj_membership_cancel_paypal', ['id' => $billing->getId()], true));
        // Create payment
        $payment = new Payment();
        $payment->setExperienceProfileId($this->getWebProfileExperience());
        $payment->setRedirectUrls($redirectUrls);
        $payment->setIntent("sale");
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->create($this->getApiContext());
        $links = $payment->getLinks();

        return $this->redirect($links[1]->toArray()['href']);
    }

    /**
     * Show sepamandate
     *
     * @Route("/execute-payment/{id}", name="tj_membership_execute_paypal")
     * @param Request $request
     * @param Billing $billing
     * @return Response
     */
    public function executePaymentAction(Request $request, Billing $billing)
    {
        if (!$this->isGranted('execute_payment_using_paypal', $billing)) {
            throw $this->createNotFoundException();
        }
        $this->getEM()->getConnection()->beginTransaction();
        try {
            $user = $this->getUser();
            $profile = $user->getProfile();
            $paymentId = $request->query->get('paymentId');
            $payerId = $request->query->get('PayerID');
            $payment = new Payment();
            $execution = new PaymentExecution();
            // Set Payment Details
            $payment->setId($paymentId);
            $execution->setPayerId($payerId);
            $payment->execute($execution, $this->getApiContext());
            $completed = $this->getEM()->getRepository(BillingStatus::class)->findOneByName(BillingStatus::COMPLETE);
            $billing->setBillingStatus($completed);
            // Order Event
            $event = new OrderEvent($billing);
            $this->dispatcher->dispatch(MembershipEvents::MEMBERSHIP_ORDER, $event);
            // Delete Notifications
            $this->readNotification($user, 'become_member', $user, null, false);
            $this->readNotification($user, 'membership_about_expire', $user, null, false);
            // Send Email/Notifications
            $this->mailer->sendBillingEmailMessage($billing);
            $this->sendNotifications($billing, $profile);
            $this->getEM()->flush();
            $this->addFlash('dashboard', ['success' => $this->getTranslator()->trans("flash.success.membership.complete.thanks")]);
            $this->getEM()->getConnection()->commit();
        } catch (\Exception $err) {
            $this->getEM()->getConnection()->rollback();
            $this->addFlash('dashboard', ['error' => $this->getTranslator()->trans("flash.message.error.membership.problem")]);
        }
        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * Cancel payment action
     *
     * @Route("/cancel-payment/{id}", name="tj_membership_cancel_paypal")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @param Billing $billing
     */
    public function cancelPaymentAction(Billing $billing)
    {
        if (!$this->isGranted('execute_payment_using_paypal', $billing)) {
            throw $this->createNotFoundException();
        }
        $em = $this->getEM();
        $booking = $billing->getBooking();
        $em->remove($booking);
        $em->remove($billing);
        $em->flush();
        $this->addFlash('dashboard', ['warning' => $this->getTranslator()->trans("flash.warning.membership.canceled.payment")]);
        return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
    }

    /**
     * @return ApiContext
     */
    private function getApiContext()
    {
        $apiContext = new ApiContext(new OAuthTokenCredential(
            $this->getParameter('paypalClientId'),
            $this->getParameter('paypalClientSecret')
        ));
        $mode = $this->getParameter('paypal_mode');

        // Alternatively pass in the configuration via a hashmap.
        // The hashmap can contain any key that is allowed in
        // sdk_config.ini
        $apiContext->setConfig(array(
            'http.ConnectionTimeOut' => 30,
            'http.Retry' => 1,
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => 'INFO'
        ));

        return $apiContext;
    }

    /**
     * @return string|CreateProfileResponse
     * @throws \ErrorException
     */
    private function getWebProfileExperience() {

        // Lets create an instance of FlowConfig
        $flowConfig = new FlowConfig();
        // When set to "commit", the buyer is shown an amount, and the button text will read "Pay Now" on the checkout page.
        $flowConfig->setUserAction('commit');

        // Payment Web experience profile resource
        $presentation = new Presentation();

        $presentation
            ->setLogoImage("https://www.theapolis.de/img/theapolis-logo.png")
            ->setBrandName("Theapolis Testshop Paypal")
            ->setLocaleCode("DE");

        $inputFields = new InputFields();

        $inputFields->setAllowNote(true)
            // PayPal does not display shipping address fields whatsoever
            // For digital goods, this field is required, and you must set it to 1.
            ->setNoShipping(1)
            // PayPal should not display the shipping address
            ->setAddressOverride(0);

        $webProfile = new WebProfile();
        // Parameters for flow configuration.
        $webProfile
            ->setName('Theapolis Testshop' . uniqid())
            ->setFlowConfig($flowConfig)
            ->setPresentation($presentation)
            ->setInputFields($inputFields)
            ->setTemporary(true);

        try {
            // Use this call to create a profile.
            $createProfileResponse = $webProfile->create($this->getApiContext());
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            throw new \ErrorException($ex->getMessage());
        }

        return $createProfileResponse->getId();
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
