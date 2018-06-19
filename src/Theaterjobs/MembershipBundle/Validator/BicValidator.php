<?php

namespace Theaterjobs\MembershipBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Theaterjobs\MembershipBundle\Service\Sepa;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Description of BicValidator
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_membership.bic_validator")
 * @DI\Tag("validator.constraint_validator", attributes = {"alias" = "theaterjobs_membership_bic_validator"})
 */
class BicValidator extends ConstraintValidator {

    /**
     * The Sepa Service
     *
     * @DI\Inject("theaterjobs_membership.sepa")
     * @var \Theaterjobs\MembershipBundle\Service\Sepa
     */
    public $sepa;

    public function validate($value, Constraint $constraint) {
        $isValid = $this->sepa->checkBic($value);

        if ($isValid === Sepa::SEPA_ERROR_BIC_NOT_SUPPORTED) {
            $this->context->buildViolation($constraint->messageBicNotSupported)->addViolation();
        } elseif ($isValid === Sepa::SEPA_ERROR_BIC_INVALID) {
            $this->context->buildViolation($constraint->messageBicInvalid)->addViolation();
        }
    }

}
