<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\MembershipBundle\EventListener;

use Theaterjobs\MembershipBundle\Entity\SepaMandate;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Description of SepamandateListener
 *
 * @category EventListener
 * @package  Theaterjobs\MembershipBundle\EventListener
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class SepamandateListener {

    public function prePersist(SepaMandate $sepamandate, LifecycleEventArgs $event) {
        $profile = $sepamandate->getDebitAccount()->getProfile();
        $mandateReference = null;
        if ($profile) {
            $count = $profile->getDebitAccounts()->count();
            $mandateReference = $profile->getId() . "-$count-THEAPOLIS";
        } else {
            throw new EntityNotFoundException("No Profile in Sepamandate");
        }

        $sepamandate->setMandatereference($mandateReference);
        $sepamandate->setPath("theaterjobs-" . $mandateReference . ".pdf");
    }

}
