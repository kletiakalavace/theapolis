<?php

namespace Theaterjobs\VATBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * ValidNumber
 *
 * @category Validation
 * @package  Theaterjobs\VATBundle\Validator\Constraints
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @Annotation
 */
class ValidNumber extends Constraint {

    public $message = 'This is not a valid VAT-number';
    public $messageInvalidCountry = 'Invalid Country Code!';
    public $messageInvalidVat = 'Invalid Vat Number!';
    public $messageVatNotValid = 'Vat is not validated against vies!';
    public $messageNetwork = 'Invalid Network!';

    public function validatedBy() {
        return 'theaterjobs_vat_vatvalidator';
    }

}
