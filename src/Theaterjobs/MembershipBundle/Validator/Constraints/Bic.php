<?php

namespace Theaterjobs\MembershipBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of Bic
 *
 * @category Validation
 * @package  Theaterjobs\MembershipBundle\Validator\Constraints
 * @author   Jana Kaszas <jana@theaterjobs.de>, Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Annotation
 */
class Bic extends Constraint {

    public $messageBicNotSupported = 'Das SEPA-Lastschrift-Einzugsverfahren, wird von Ihrer Bank nicht unterstützt';
    public $messageBicInvalid = 'Die eingegebene BIC ist ungültig!';

    public function validatedBy() {
        return 'theaterjobs_membership_bic_validator';
    }

}
