<?php

namespace Theaterjobs\MembershipBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MainBundle\Utility\Traits\EmailTrait;
use Theaterjobs\MembershipBundle\Entity\Billing as BillingEntity;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Mailer Service
 *
 * @category Service
 * @package  Theaterjobs\MembershipBundle\Utils
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_membership.mailer")
 */
class Mailer
{
    use EmailTrait;

    /** @DI\Inject("%theaterjobs_membership.email_from%") */
    public $emailFrom;

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /**
     * @DI\Inject("templating")
     * @var \Symfony\Component\Templating\EngineInterface
     */
    public $templating;

    /** @DI\Inject("translator") */
    public $trans;

    /**
     * Send Email to the user after success paypal/sofort payment
     * @param BillingEntity $billing
     */
    public function sendBillingEmailMessage(BillingEntity $billing)
    {
        $subject = $this->trans->trans('checkout.email.subject', [], 'emails');
        $body = $this->templating->render("TheaterjobsMembershipBundle:Mailer:sendBillingEmail.html.twig", ['billing' => $billing]);
        $email = $billing->getBooking()->getProfile()->getUser()->getEmail();

        $this->sendEmailMessage(
            $subject,
            $body,
            $this->emailFrom,
            $email,
            'text/html'
        );
    }


    /**
     * @TODO Add case description
     * @param User $user
     */
    public function sendMembershipExpirationEmail($user)
    {
        $subject = $this->trans->trans('membership.expiration.paypal_sofort.email.subject', [], 'emails');
        $body = $this->templating->render("TheaterjobsMembershipBundle:Mailer:sendMembershipExpirationEmail.html.twig", ['user' => $user]);
        $email = $user->getProfile()->getUser()->getEmail();

        $this->sendEmailMessage(
            $subject,
            $body,
            $this->emailFrom,
            $email,
            'text/html'
        );
    }
}