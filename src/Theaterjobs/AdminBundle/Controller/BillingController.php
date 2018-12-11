<?php

namespace Theaterjobs\AdminBundle\Controller;

use Carbon\Carbon;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\AdminBundle\Entity\SepaXmlBilling;
use Theaterjobs\AdminBundle\Form\AdminBillingType;
use Theaterjobs\AdminBundle\Form\SepaXmlSearchType;
use Theaterjobs\AdminBundle\Model\AdminBillingSearch;
use Theaterjobs\AdminBundle\Model\SepaXmlSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * Billing controller.
 *
 * @Route("/invoices")
 */
class BillingController extends BaseController
{
    /**
     * Lists all People entities.
     *
     * @Route("/index", name="admin_invoices_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $billingSearch = new AdminBillingSearch();

        $adminBillingSearchForm = $this->createGeneralSearchForm(AdminBillingType::class,
            $billingSearch,
            [],
            'admin_invoices_load'
        );

        return $this->render('TheaterjobsAdminBundle:Billing:index.html.twig', [
            'form' => $adminBillingSearchForm->createView()
        ]);
    }

    /**
     * Load billing async.
     *
     * @Route("/load_admin_invoices", name="admin_invoices_load", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadBillings(Request $request)
    {
        $billingSearch = new AdminBillingSearch();

        $adminBillingSearchForm = $this->createGeneralSearchForm(AdminBillingType::class,
            $billingSearch,
            [],
            'admin_invoices_load'
        );

        $paginator = $this->getPaginator();

        $adminBillingSearchForm->handleRequest($request);
        $adminBillingSearch = $adminBillingSearchForm->getData();

        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $result = $this->container->get('fos_elastica.index.theaterjobs.billing');
        $query = $this->container->get('fos_elastica.manager')->getRepository(Billing::class)->adminBillingSearch($adminBillingSearch);

        $paginationBilling = $paginator->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query, // \Elastica\Query
                [], // options
                new ElasticaToRawTransformer()
            ), $pageNr, $rows);

        $iTotalRecords = $paginationBilling->getTotalItemCount();

        $records = [];
        $records["data"] = [];

        foreach ($paginationBilling as $billing) {
            $booking = $billing->booking;
            $profile = $booking['profile'];
            $userAccount = $this->generateUrl('tj_user_account_settings', ['slug' => $profile['slug']]);
            $userColumn = sprintf("<a href=%s >%s</a>", $userAccount, $profile['full_name']);
            $billingAddress = $billing->billingAddress;
            $billingCountryColumn = $billingAddress['country'];
            $billingNrColumn = $billing->number;
            $billingIbanColumn = $billing->iban;
            $bookingPaymentMethod = $booking['paymentmethod'];

            $bookingPaymentMethodColumn = $bookingPaymentMethod['short'];

            $billingCreationColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', [
                'date' => $billing->createdAt
            ])->getContent();


            $createdAt = new Carbon($billing->createdAt);
            $kindOfOrder = AdminBillingSearch::ORDER_TYPE_NEW;

            $billingQuery = $this->container->get('fos_elastica.manager')->getRepository(Billing::class)->countBillingsProfile($profile['id'], $createdAt->toDateTimeString());
            $profileBillingsCount = $result->search($billingQuery)->getTotalHits();

            if ($profileBillingsCount > 1) {
                // only for direct Payment we have many invoice connected to one booking
                if ($bookingPaymentMethodColumn == Paymentmethod::DIRECT_DEBIT) {
                    $billingStatusQuery = $this->container->get('fos_elastica.manager')->getRepository(Billing::class)->countBookings($booking['id']);
                    $profileBillingsStatusCount = $result->search($billingStatusQuery)->getTotalHits();

                    if ($profileBillingsStatusCount > 1) {
                        $kindOfOrder = AdminBillingSearch::ORDER_TYPE_CONTINUE;
                    }
                } else {
                    $kindOfOrder = AdminBillingSearch::ORDER_TYPE_AGAIN;
                }
            }

            $records["data"][] = [
                $userColumn,
                $billingNrColumn,
                $billingIbanColumn,
                $bookingPaymentMethodColumn,
                $billingCountryColumn,
                $billingCreationColumn,
                $kindOfOrder
            ];
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;
        return new JsonResponse($records);
    }

    /**
     *
     * @Route("/sepa-xml", name="admin_billings_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function billingAction()
    {
        $sepaXmlSearch = new SepaXmlSearch();
        $adminSepaXmlSearchForm = $this->createGeneralSearchForm(SepaXmlSearchType::class,
            $sepaXmlSearch,
            [],
            'admin_billings_search'
        );

        return $this->render('TheaterjobsAdminBundle:Billing:sepaXml.html.twig', [
            'form' => $adminSepaXmlSearchForm->createView()
        ]);
    }

    /**
     *
     * @Route("/search/sepa-xml", name="admin_billings_search", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function billingSearchAction(Request $request)
    {
        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $sepaXmlSearch = new SepaXmlSearch();

        $adminSepaXmlSearchForm = $this->createGeneralSearchForm(SepaXmlSearchType::class,
            $sepaXmlSearch,
            [],
            'admin_billings_search'
        );

        $adminSepaXmlSearchForm->handleRequest($request);
        $adminSepaXmlSearch = $adminSepaXmlSearchForm->getData();

        $billingSepas = $this->getEM()->getRepository(SepaXmlBilling::class)->getSepaXml($adminSepaXmlSearch);
        $paginator = $this->getPaginator();

        $paginatedBillingSepas = $paginator->paginate($billingSepas, $pageNr, $rows);
        $records = [];
        $records["data"] = [];
        $iTotalRecords = $paginatedBillingSepas->getTotalItemCount();

        /** @var SepaXmlBilling $billingSepa */
        foreach ($paginatedBillingSepas as $billingSepa) {
            $data = [];
            // File Name
            $fileName = $billingSepa['fileName'];
            $fileUrl = $this->generateUrl('admin_billing_download_sepa_xml', ['id' => $billingSepa['id']]);
            $data[] = "<a id=\"sepa-name\" href=\"$fileUrl\">$fileName</a>";

            if (isset($billingSepa['user'])) {
                // Admin that downloaded
                $userName = $billingSepa['user'];
                $profUrl = $this->generateUrl('tj_profile_profile_show', ['slug' => $billingSepa['profileSlug']]);
                $date = $this->render('@TheaterjobsInserate/Partial/date_formatted.html.twig', array('date' => $billingSepa['lastDownloadedAt']))->getContent();
                $data[] = "<a href=\"$profUrl\">$userName</a>";
                $data[] = $date;
            } else {
                $data[] = $this->getTranslator()->trans("None");
                $data[] = $this->getTranslator()->trans("None");
            }
            $records["data"][] = $data;
        }

        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }

    /**
     * @Route("/download/{id}/sepa-xml", name="admin_billing_download_sepa_xml")
     * @Method("GET")
     * @param SepaXmlBilling $sepaXmlBilling
     * @return Response | JsonResponse
     */
    public function downloadSepaXmlAction(SepaXmlBilling $sepaXmlBilling)
    {
        $fileDir = $this->get('kernel')->getRootDir() . SepaXmlBilling::PATHDIR;
        $fileName = $sepaXmlBilling->getFileName();

        // Set file downloaded once
        $sepaXmlBilling->setLastDownloadedAt(Carbon::now());
        $sepaXmlBilling->setLastDownloadedBy($this->getUser());
        $this->getEM()->persist($sepaXmlBilling);
        $this->logActivity($fileName);
        $this->getEM()->flush();
        $response = new Response();

        try {
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type($fileDir . $fileName));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($fileName) . '";');
            $response->headers->set('Content-length', filesize($fileDir . $fileName));
            $xml = file_get_contents($fileDir . $fileName);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $this->getTranslator()->trans('billing.sepa.file.not.found')]);
        }

        $response->sendHeaders();
        $response->setContent($xml);
        return $response;
    }

    /**
     * Log activity for admin
     * @param $fileName
     */
    public function logActivity($fileName)
    {
        $dispatcher = $this->get('event_dispatcher');
        $params = ['fileName' => $fileName];
        $message = $this->getTranslator()->trans('tj.admin.billing.sepaxml.downloaded %fileName%', $params, 'activity');
        $uacEvent = new UserActivityEvent($this->getUser(), $message, true);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);
    }
}