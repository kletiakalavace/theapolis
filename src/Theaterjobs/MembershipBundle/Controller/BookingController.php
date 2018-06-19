<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Theaterjobs\InserateBundle\Utility\ESUserActivity;
use Theaterjobs\MainBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\MainBundle\Utility\Traits\ReadNotificationTrait;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\DebitAccount;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\MembershipBundle\Form\Type\BookingType;
use Theaterjobs\MembershipBundle\Security\BookingVoter;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\VATBundle\Service\VATService;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\MembershipBundle\Event\OrderEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * The BookingController.
 *
 * Controller for a Booking
 *
 * @category Controller
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Route("/booking")
 */
class BookingController extends BaseController
{
    use ESUserActivity, ReadNotificationTrait;

    /** @DI\Inject("session") */
    private $session;

    /** @DI\Inject("theaterjobs_membership.price") */
    private $price;

    /**
     * @DI\Inject("theaterjobs_membership.billing")
     * @var \Theaterjobs\MembershipBundle\Service\Billing
     */
    private $billing;

    /** @DI\Inject("event_dispatcher") */
    private $dispatcher;

    /** @DI\Inject("translator") */
    private $trans;

    /**
     * Action for a new Booking
     * @Route("/new", name="tj_membership_booking_new", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function newAction(Request $request)
    {
        if (!$this->isGranted(BookingVoter::BUY_MEMBERSHIP)) {
            return $this->redirect($this->generateUrl('tj_main_dashboard_index'));
        }
        $em = $this->getEM();
        $user = $this->getUser();
        $profile = $user->getProfile();
        // Get DB Entities
        $membership = $em->getRepository(Membership::class)->findOneBySlug(Membership::yearly);
        $oldDebitAccount = $em->getRepository(DebitAccount::class)->findOneByProfile($profile);
        $currPayMethod = $em->getRepository(Paymentmethod::class)->paymentMethodByProfile($profile);
        // New booking to persist
        $booking = new Booking();
        $booking->setProfile($profile);
        $form = $this->createCreateForm(BookingType::class, $booking, ['profile' => $profile], 'tj_membership_booking_new');
        // New debit Entity or old cloned
        $newDebit = $oldDebitAccount ? clone $oldDebitAccount : new DebitAccount();
        $form->get('debitAccount')->setData($newDebit);
        $preCalculate = $this->price->calculateToSave('DE', $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $paymentMethod = $form["paymentmethod"]["title"]->getData();
                $billingAddress = $profile->getBillingAddress();
                $billingAddress->setProfile($profile);
                $booking->setPaymentmethod($paymentMethod);
                $booking->setMembership($membership);
                $profile->addBooking($booking);
                $em->persist($booking);
                $em->persist($profile);
                $calc = $this->price->calculateToSave($billingAddress->getCountry(), $membership, $billingAddress->getVatId(), $paymentMethod->isPaypal());

                $billing = $this->billing->createBilling($booking, $calc);
                $billing->setBillingAddress($billingAddress->serialize());
                $this->session->set('billing', $billing);

                if ($paymentMethod->isDebit()) {
                    // Get data from db not from entity, check if changing from sofort/paypal to debit
                    $changingToDebit = $currPayMethod && ($currPayMethod->isPaypalorSofort());
                    $sepaMandate = $this->getSepaMandate($newDebit, $oldDebitAccount, $changingToDebit, $request->getClientIp());
                    // Set billing data
                    $billing->setSepa($sepaMandate);
                    $billing->setIban($newDebit->getIban());
                    $billing->setAccountHolder($newDebit->getAccountHolder());
                    $event = new OrderEvent($billing);
                    $this->dispatcher->dispatch(MembershipEvents::MEMBERSHIP_ORDER, $event);
                    $this->dispatchNotifications($user, $paymentMethod, $billing, $changingToDebit);
                }
                $em->persist($billing);
                $em->flush();
                // Move to Sofort Controller
                if ($paymentMethod->isSofort()) {
                    $em->getConnection()->commit();
                    return $this->forward('TheaterjobsMembershipBundle:Sofort:makePaymentUsingSofort', ['id' => $billing->getId()]);
                }
                // Move to Paypal Controller
                if ($paymentMethod->isPaypal()) {
                    $em->getConnection()->commit();
                    return $this->forward('TheaterjobsMembershipBundle:PayPal:makePaymentUsingPayPal', ['id' => $billing->getId()]);
                }

                $this->addFlash('dashboard', ['success' => $this->trans->trans("flash.success.membership.complete.thanks")]);
                $this->get('theaterjobs_membership.mailer')->sendBillingEmailMessage($billing);
                $this->session->remove('membership');
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                // rollback if something goes wrong, keep db clean
                $em->getConnection()->rollback();
                throw $e;
            }
            return new RedirectResponse($this->generateUrl('tj_main_dashboard_index'));
        }

        return $this->render('TheaterjobsUserBundle:AccountSettings/Modal:payment_method.html.twig', array(
            'form' => $form->createView(),
            'euCountries' => json_encode(VATService::$validCountries),
            'euMappedCountries' => json_encode(VATService::$euCountryMapping),
            'membership' => $membership,
            'preCalculate' => $preCalculate,
            'isPaypal' => false
        ));
    }

    /**
     * Action for a new Booking
     *
     * @Route("/calculate/", name="tj_membership_calculate_payment", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function calculatePaymentAction(Request $request)
    {
        //manually casting string to boolean
        $isPaypal = $request->query->get('isPaypal') == "true";
        $membershipSlug = $request->query->get('membership');
        $country = $request->query->get('country');
        $vat = $request->query->get('vat_number');

        $membership = $this->getEM()->getRepository(Membership::class)->findOneBy(['slug' => $membershipSlug]);
        $response = $this->price->calculateToSave($country, $membership, $vat, $isPaypal);

        $template = $this->renderView('TheaterjobsMembershipBundle:Booking:_preCalculate.html.twig', [
            'membership' => $membership,
            'preCalculate' => $response,
            'isPaypal' => $isPaypal
        ]);
        return new Response($template);
    }

    /**
     * Send notification to user that we will recieve money
     *
     * @param $billing
     */
    public function sendNotification($billing)
    {
        $notification = new Notification();
        $notification
            ->setTitle($this->trans->trans("notification.theapolis.will.get.money.mail.subject"))
            ->setCreatedAt(new \DateTime())
            ->setDescription('')
            ->setRequireAction(false)
            ->setLink('tj_user_account_settings')
            ->setLinkKeys(array('tab' => 'billing'));

        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(Billing::class)
            ->setObjectId($billing->getId())
            ->setNotification($notification)
            ->setUsers($this->getUser())
            ->setType('order_received')
            ->setFlush(false);
        $this->dispatcher->dispatch('notification', $notificationEvent);
    }

    /**
     * @param DebitAccount $newDebit
     * @param DebitAccount $oldDebitAccount
     * @param bool $changingToDebit
     * @param string $ip
     * @return mixed
     */
    private function getSepaMandate($newDebit, $oldDebitAccount, $changingToDebit, $ip = '127.0.0.1')
    {
        $profile = $this->getProfile();
        if (!$newDebit->isEqual($oldDebitAccount) || $changingToDebit) {
            $sepaMandate = $this->get('theaterjobs_membership.sepa')->generateSepa($profile, $ip);
            $this->getEM()->persist($sepaMandate);
            if ($oldDebitAccount) {
                $newDebit = $oldDebitAccount->update($newDebit);
            }
            $newDebit->setProfile($profile);
            $this->getEM()->persist($newDebit);
            return $sepaMandate;
        }
        return $profile->getSepaMandates()->last();
    }

    /**
     * @param User $user
     * @param Paymentmethod $paymentMethod
     * @param Billing $billing
     * @param bool $changingToDebit
     */
    private function dispatchNotifications($user, $paymentMethod, $billing, $changingToDebit)
    {
        // Delete existing NRA notifications
        $this->readNotification($user, 'become_member', $user, null, false);
        $this->readNotification($user, 'membership_about_expire', $user, null, false);
        $log = $this->trans->trans('tj.user.activity.payment.made.with %paymentMethod%', ["%paymentMethod%" => $paymentMethod->getTitle()], 'activity');
        $this->logUserActivity($user, $log, false, null, null, false);
        // Trigger Membership event
        $this->sendNotification($billing);
    }
}