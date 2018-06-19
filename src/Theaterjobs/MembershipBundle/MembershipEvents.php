<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\MembershipBundle;

/**
 * Description of StoreEvents
 *
 * @category Event
 * @package  Theaterjobs\MembershipBundle\Event
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
final class MembershipEvents {

    const MEMBERSHIP_ORDER = 'membership.order';
    const MEMBERSHIP_PAY = 'membership.pay';
    const MEMBERSHIP_EXPIRED = 'membership.expired';

}
