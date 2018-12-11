<?php

namespace Theaterjobs\MembershipBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of NoCompanyWithVatId
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator\Constraints
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Annotation
 */
class NoCompanyWithVatId extends Constraint {

    public $message = 'Sie haben eine Umsatzsteuer-ID angegeben. Geben Sie auch einen Firmennamen an!';

    public function validatedBy() {
        return 'theaterjobs_membership_no_company_with_vat_id_validator';
    }

    public function getTargets() {
        return array(self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT);
    }

}
