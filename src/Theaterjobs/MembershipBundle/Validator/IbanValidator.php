<?php

namespace Theaterjobs\MembershipBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Theaterjobs\MembershipBundle\Sepa\Sepa;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Description of IbanValidator
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_membership.iban_validator")
 * @DI\Tag("validator.constraint_validator", attributes = {"alias" = "theaterjobs_membership_iban_validator"})
 */
class IbanValidator extends ConstraintValidator {

    /**
     * The Sepa Service
     *
     * @DI\Inject("theaterjobs_membership.sepa")
     * @var Sepa
     */
    public $sepa;

    public function validate($value, Constraint $constraint) {

        $iban = strtoupper(trim(str_replace(' ', '', $value)));

        if (!$this->sepa->checkIban($iban)) {
            $this->context->buildViolation($constraint->messageIbanInvalid)->addViolation();
        }
    }

}
