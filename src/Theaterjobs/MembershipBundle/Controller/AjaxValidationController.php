<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\MainBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * AjaxValidationController
 *
 * @category Controller
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class AjaxValidationController extends BaseController {

    /** @DI\Inject("theaterjobs_membership.sepa") */
    public $sepa;

    /**
     * @var \Theaterjobs\VATBundle\Service\VATService
     * @DI\Inject("theaterjobs_vat.vatservice")
     */
    public $vat;

    /**
     * Action to validate the iban
     *
     * @Route("/validate/iban", name="tj_membership_validate_iban", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function ibanValidationAction(Request $request)
    {
        $value = $request->query->get('iban');
        $iban = strtoupper(trim(str_replace(' ', '', $value)));
        if ($this->sepa->checkIban($iban)) {
            return new Response('true');
        } else {
            return new Response('false');
        }
    }

    /**
     * Action for a new Booking
     *
     * @Route("/validate/vat", name="tj_membership_validate_vat", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function vatValidationAction(Request $request)
    {
        $number = str_replace(" ", "", $request->query->get('vat'));
        try {
            $this->vat->validate($number);
            return new Response('true');
        } catch (\Exception $e) {
            return new Response('false');
        }
    }

    /**
     * Action for a new Booking
     *
     * @Route("/generate/bic", name="tj_membership_generate_bic", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function generateBicAction(Request $request)
    {
        $data = $this->sepa->generateBic($request->query->get('iban'));
        return new JsonResponse($data);
    }

}
