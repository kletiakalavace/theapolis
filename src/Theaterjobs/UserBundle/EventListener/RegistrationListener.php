<?php

namespace Theaterjobs\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo;

/**
 * RegistrationListener
 *
 * @category EventListener
 * @package  Theaterjobs\UserBundle\EventListener
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class RegistrationListener implements EventSubscriberInterface {

    protected $container;

    /**
     * Constructor
     *
     * @param UserManagerInterface $userManager The user manager.
     * @param ObjectManager        $om          The object manager.
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents() {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED
            => 'onRegistrationCompletedSuccess',
            FOSUserEvents::REGISTRATION_CONFIRMED
            => 'onRegistrationConfirmedSuccess',
//            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onImplicitLogin',
//            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    /**
     * Set Response on confirmed registration
     *
     * @param FOS\UserBundle\Event\FilterUserResponseEvent  $event
     */
    public function onRegistrationConfirmedSuccess(FilterUserResponseEvent $event) {
        $user = $event->getUser();
        $logger = $this->container->get('monolog.logger.registration');

        $logger->info("Registration of {$user->getUsername()} confirmed");   
        $dispatcher = $this->container->get('event_dispatcher');
        $uacEvent = new \Theaterjobs\UserBundle\Event\UserActivityEvent($user, $this->container->get('translator')->trans('tj.user.activity.confirmed.registration.successfully', array(), 'activity'));
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);
    }

    /**
     * Logs the completed Registration
     *
     * @param FilterUserResponseEvent $event
     * @TODO send a confirmation email to activate a user
     */
    public function onRegistrationCompletedSuccess(FilterUserResponseEvent $event) {        
        $em = $this->container->get('doctrine')->getEntityManager('default');        
        $user = $event->getUser();
        $user->setUsername($user->getEmail());
        $user->getProfile()->setSubtitle($user->getProfile()->getFirstName() . ' ' . $user->getProfile()->getLastName());
        $user->getProfile()->setProfileName(false);
        $profileAllowedTo = new ProfileAllowedTo();
        $em->persist($profileAllowedTo);
        $user->getProfile()->setProfileAllowedTo($profileAllowedTo);
        $user->getProfile()->setInAdminCheckList(0);
        $user->getProfile()->setIsRevokedBefore(false);
        $user->addRole('ROLE_USER');
        $user->setEnabled(false);
        $user->getProfile()->setShowWizard(true);
        $this->container->get('fos_user.user_manager')->updateUser($user);
        $logger = $this->container->get('monolog.logger.registration');
        $logger->info("Registration of {$user->getUsername()} completed");
        $dispatcher = $this->container->get('event_dispatcher');
        $uacEvent = new \Theaterjobs\UserBundle\Event\UserActivityEvent($user, $this->container->get('translator')->trans('tj.user.activity.registered.successfully', array(), 'activity'));
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);
    }

    public function onImplicitLogin(\FOS\UserBundle\Event\UserEvent $event) {
        $user = $event->getUser();
        //var_dump('tests');
        //exit;
        $user->setLastLogin(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof \FOS\UserBundle\Model\UserInterface) {
            $user->setLastLogin(new \DateTime());
            $this->container->get('fos_user.user_manager')->updateUser($user);
        }
    }

}
