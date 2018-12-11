<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Theaterjobs\MainBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingAddress;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\SepaMandate;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\VATBundle\Service\VATService;

/**
 * The Membership Controller.
 *
 * Provides the Overview of the Memberships available
 *
 * @category Controller
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Route("/")
 */
class MembershipController extends BaseController
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    private $em;

    /** @DI\Inject("session") */
    private $session;

    /** @DI\Inject("translator") */
    private $translator;

    /** @DI\Inject("theaterjobs_membership.price") */
    private $price;

    /**
     * @DI\Inject("knp_snappy.pdf")
     * @var \Knp\Snappy\GeneratorInterface
     */
    private $pdfGenerator;

    /**
     * The index action.
     *
     * @param Request $request Represents a HTTP request.
     * @Route("/", name="tj_membership_index")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $this->session->remove('membership');
        $this->session->remove('paymentmethod');
        $this->session->remove('extendMembership');

        if (!$this->isGranted('ROLE_ADMIN') && !$this->session->get('pay_profile')) {
            $bookings = $this->getEM()->getRepository('TheaterjobsMembershipBundle:Billing')->findPendingBillsByProfile($this->getUser()->getProfile());

            if ($bookings['pendingBills'] > 0) {
                $this->addFlash('accountSettings', ['warning', $this->getTranslator()->trans("flash.warning.membership.pending.exists")]);
                return $this->redirect($this->generateUrl('tj_user_account_settings', ['tab' => 'billing']));
            }

            if ($this->getUser()->getExtendMembership()) {
                $this->addFlash('accountSettings', ['warning', $this->getTranslator()->trans("flash.warning.prepayment.membership.extended.once")]);
                return $this->redirect($this->generateUrl('tj_user_account_settings', array('tab' => 'billing')));
            }

            if ($request->query->get('extend') !== null) {
                $this->session->set('extendMembership', $request->query->get('extend'));
            }

            // @TODO Check if we still use this role
            if ($this->isGranted('ROLE_ABO')) {
                $this->addFlash('accountSettings',['warning' => $this->translator->trans('flash.error.membership.order.processing', [], 'flashes')]);
                return $this->redirect($this->generateUrl('tj_user_account_settings', ['tab' => 'billing']));
            }
        }
        $memberships = $this->em->getRepository(Membership::class)->findAll();
        return $this->render('TheaterjobsMembershipBundle:Membership:index.html.twig', ['memberships' => $memberships]);
    }


    /**
     * @Route("/invoices-archive/{slug}", name="tj_membership_invoice", defaults={"slug" = null})
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('can_see_invoices', profile)")
     */
    public function invoiceArchiveAction(Profile $profile)
    {
        $bills = $this->em->getRepository(Billing::class)->findByProfile($profile);

        return $this->render('TheaterjobsMembershipBundle:Billing:invoiceArchive.html.twig', [
            'bills' => $bills,
            'profile' => $this->isGranted(User::ROLE_ADMIN) ? $profile : $this->getProfile()
        ]);
    }

    /**
     * @param $bill
     * @return Response
     * @Route("/download-archive-bill/{id}", name="tj_membership_download_invoice_bill")
     */
    public function downloadInvoiceAction(Billing $bill)
    {
        $billAddr = $bill->getBillingAddress();
        $membership = $this->em->getRepository(Membership::class)->findOneBySlug(Membership::yearly);
        $vat = isset($billAddr->vatId) ? $billAddr->vatId : null;
        $isPaypal = $bill->getBooking()->getPaymentmethod()->isPaypal();
        $preCalculate = $this->price->calculateToSave($billAddr->country, $membership, $vat, $isPaypal);
        $this->pdfGenerator->setOption('encoding', 'UTF-8');
        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->renderView(
                'TheaterjobsMembershipBundle:Pdf:billingPdf.html.twig', [
                    'preCalculate' => $preCalculate,
                    'billing' => $bill,
                    'euCountries' => VATService::$validCountries
                ]
            )
        );

        $filename = $bill->getFileName(false);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', "attachment;filename=\"$filename.pdf\"");
        $response->setContent($pdf);

        return $response;
    }

    /**
     * @param $bill
     * @return Response
     * @Route("/download-archive-sepa/{id}", name="tj_membership_download_invoice_sepa")
     * @Security("is_granted('can_download_sepa', bill)")
     */
    public function downloadInvoiceSepaAction(Billing $bill)
    {
        $debitAccount = $bill->getBooking()->getProfile()->getDebitAccount();
        $sepa = $bill->getSepa();
        $this->pdfGenerator->setOption('encoding', 'UTF-8');
        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->renderView(
                'TheaterjobsMembershipBundle:Pdf:sepaPdf.html.twig', [
                    'sepamandate' => $sepa,
                    'debitAccount' => $debitAccount,
                    'billing' => $bill
                ]
            )
        );

        $filename = $bill->getFileName(true);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', "attachment;filename=\"$filename.pdf\"");
        $response->setContent($pdf);
        return $response;
    }

    /**
     * @param $id
     * @param $slug
     * @return Response
     * @internal param Request $request
     *
     * @Route("/downloadSepa/{id}/{slug}", name="tj_membership_download_current_sepa", defaults={"slug" = null})
     */
    public function downloadCurrentSepa($id, $slug = null)
    {
        if ($slug) {
            $profile = $this->getEM()->getRepository(Profile::class)->findOneBy(['slug' => $slug]);
        } else {
            $profile = $this->getProfile();
        }

        $debitAccount = $profile->getDebitAccount();

        $sepa = $this->getRepository('TheaterjobsMembershipBundle:SepaMandate')->findOneBy(['id' => $id]);

        $this->pdfGenerator->setOption('encoding', 'UTF-8');
        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->renderView(
                'TheaterjobsMembershipBundle:Pdf:currentSepaPdf.html.twig', array(
                    'sepamandate' => $sepa,
                    'debitAccount' => $debitAccount,
                )
            )
        );
        $filename = explode('.', $sepa->getPath())[0];

        $response = new Response();
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '.pdf"');
        $response->setContent($pdf);
        return $response;
    }
}
