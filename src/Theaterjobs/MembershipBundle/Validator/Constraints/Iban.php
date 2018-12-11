<?php

namespace Theaterjobs\MembershipBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of BicValidator
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator\Constraints
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Annotation
 */
class Iban extends Constraint {

    public $messageIbanInvalid = 'Die eingegebene Iban ist ungültig!';

    public function validatedBy() {
        return 'theaterjobs_membership_iban_validator';
    }

}
