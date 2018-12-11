<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\MembershipBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Description of NoCompanyWithVatIdValidator
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_membership.company_validator")
 * @DI\Tag("validator.constraint_validator", attributes = {"alias" = "theaterjobs_membership_no_company_with_vat_id_validator"})
 */
class NoCompanyWithVatIdValidator extends ConstraintValidator {

    public function validate($value, Constraint $constraint) {
//        $vatId = $this->context->getRoot()->get('vatId')->getData();
//
//        if (!empty($vatId) && empty($value)) {
//            $this->context->buildViolation($constraint->message)->addViolation();
//        }
    }

}
