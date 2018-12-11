<?php

namespace Theaterjobs\UserBundle\Mailer;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Mailer\Mailer as BaseMailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Send emails in a centralized way
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class Mailer extends BaseMailer
{
    /** @var \Theaterjobs\MainBundle\Mailer\Mailer */
    protected $baseMailer;

    /** @var Translator $translators */
    protected $translator;

    /** @var  EntityManager $em */
    protected $em;

    protected $fromEmail;

    protected $request;

    /**
     * Mailer constructor.
     * @param \Swift_Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param array $parameters
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        array $parameters,
        TranslatorInterface $translator,
        EntityManager $em,
        $fromEmail,
        $baseMailer
    )
    {
        parent::__construct($mailer, $router, $templating, $parameters);
        $this->translator = $translator;
        $this->em = $em;
        $this->fromEmail = $fromEmail;
        $this->baseMailer = $baseMailer;
    }

    public function setRequest(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {

        if (empty($this->request->get('choice'))) {
            $choice = $_SESSION['_sf2_attributes']['registrationChoice'];
        } else {
            $choice = $this->request->get('choice');
        }

        $template = $this->parameters['confirmation.template'];

        $url = $this->router->generate('fos_user_registration_confirm_choice', array('token' => $user->getConfirmationToken(), 'choice' => $choice), UrlGeneratorInterface::ABSOLUTE_URL);
        switch ($choice) {
            case 'member':
                $content = "tj.registration.mailtext.plus";
                break;
            case 'free':
                $content = "tj.registration.mailtext.free";
                break;
            case 'job':
                $content = "tj.registration.mailtext.jobs";
                break;
            default:
                $content = "tj.registration.mailtext.free";
        }

        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url,
            'content' => $content,
            'choice' => $choice
        ));

        $this->baseMailer->sendRenderedEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }


    /**
     * @param $case
     * @param $job
     */
    public function sendEmailOnJobPublish($case, Job $job)
    {
        $url = $this->router->generate('tj_inserate_job_route_confirm_job_form_email', array('token' => $job->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $user = $job->getUser();
        $title = $job->getTitle();
        $email = $user->getEmail();
        switch ($case) {
            case 'publishSuccess':
                $content = "tj.job.publish.mailtext.success";
                break;
            case 'publishSuccessNowTeamMember':
                $content = "tj.registration.mailtext.publishSuccessNowTeamMember";
                break;
            case 'confirmEmailForPublish':
                $content = "tj.registration.mailtext.confirmEmailForPublish";
                break;
            case 'confirmEmailOfJobForPublish':
                $email = $job->getEmail();
                $content = "tj.registration.mailtext.confirmEmailForPublish";
                break;
            case 'adminWillCheck':
                $content = "tj.registration.mailtext.adminWillCheck";
                break;
            case 'jobPublishRequestForYourOrganization':
                $content = "tj.registration.mailtext.jobPublishRequestForYourOrganization";
                break;
            default:
                $content = "tj.registration.mailtext.success";
        }


        $rendered = $this->templating->render('TheaterjobsInserateBundle:Job/email:jobConfirmationEmail.html.twig', array(
            'user' => $user,
            'confirmationUrl' => $url,
            'content' => $content,
            'title' => $title
        ));

        $this->baseMailer->sendRenderedEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $email);
    }


    /**
     * @param $case
     * @param UserInterface $user
     */
    public function nameChangeRequestManagement($case, UserInterface $user)
    {
        $email = $user->getEmail();
        switch ($case) {
            case 'confirmed':
                $content = $this->translator->trans("email.namechange.request.approved");
                break;
            case 'rejected':
                $content = $this->translator->trans("email.namechange.request.rejected");
                break;
            default:
                $content = "The request you made for changing the name of your profile could not be approved approved by the admins. For more details please contact the team.";
        }

        $rendered = $this->templating->render('TheaterjobsAdminBundle:NameChangeRequests/email:nameChangeRequestNotice.html.twig', array(
            'user' => $user,
            'content' => $content
        ));

        $this->baseMailer->sendRenderedEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $email);
    }

    /**
     * Send an email to the new member of organization
     * @param $organization Organization
     * @param $user UserInterface
     */
    public function newOrganizationMember($organization, UserInterface $user)
    {
        $rendered = $this->templating->render('TheaterjobsUserBundle:UserOrganization/email:newMember.html.twig', array(
            'organization' => $organization->getName(),
            'user' => $user
        ));

        $this->baseMailer->sendRenderedEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    /**
     * Send an email to user to update bank data
     * @param Profile $profile
     */
    public function updateBankDataEmail(Profile $profile)
    {
        $body = $this->templating->render('TheaterjobsMembershipBundle:DebitAccount/email:updateDebitAccount.html.twig', ['profile' => $profile]);
        $this->baseMailer->sendEmailMessage(
            $this->translator->trans('tj.debit.account.changed.mail.subject', [], 'flashes'),
            $body,
            $this->fromEmail,
            $profile->getUser()->getEmail()
        );
    }

    /**
     * Overridden to add it on queue
     * {@inheritdoc}
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['resetting.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url,
        ));
        $this->baseMailer->sendRenderedEmailMessage($rendered, $this->parameters['from_email']['resetting'], (string)$user->getEmail());
    }
}