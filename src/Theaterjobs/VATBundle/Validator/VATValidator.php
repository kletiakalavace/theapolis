<?php

namespace Theaterjobs\VATBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * VATValidator
 *
 * @category Validation
 * @package  Theaterjobs\VATBundle\Validator
 * @author   JHeiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\Service("theaterjobs_vat.vat_validator")
 * @DI\Tag("validator.constraint_validator", attributes = {"alias" = "theaterjobs_vat_vatvalidator"})
 */
class VATValidator extends ConstraintValidator {

    /**
     * @var \Theaterjobs\VATBundle\Service\VATService
     * @DI\Inject("theaterjobs_vat.vatservice")
     */
    public $vat;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     * @DI\Inject("request_stack")
     */
    public $request;

    const SOAP_ERR = 'soaperror';

    public function validate($value, Constraint $constraint) {
        // We do not want Germany
//        $countryCode = $this->context->getRoot()->get('country')->getData();
//        //$countryCode = 'DE';
//        if (!empty($value) && $countryCode !== 'DE') {
//            try {
//                $this->vat->validate($countryCode, $value);
//            } catch (InvalidVATValidationException $exception) {
//                $this->context->buildViolation($constraint->messageVatNotValid)->addViolation();
//            } catch (InvalidCountryCodeException $exception) {
//                $this->context->buildViolation($constraint->messageInvalidCountry)->addViolation();
//            } catch (InvalidVATNumberException $exception) {
//                $this->context->buildViolation($constraint->messageInvalidVat)->addViolation();
//            } catch (VATException $exception) {
//                $this->request->getCurrentRequest()->attributes->set(self::SOAP_ERR, true);
//            }
//        }
    }

}
