<?php

namespace Theaterjobs\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 *
 *
 * @category EventListener
 * @package  Theaterjobs\UserBundle\EventListener
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class PasswordResettingListener implements EventSubscriberInterface {

    private $router;

    public function __construct(UrlGeneratorInterface $router) {
        $this->router = $router;
    }

    public static function getSubscribedEvents() {
        return [
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onPasswordResettingSuccess',
        ];
    }

    public function onPasswordResettingSuccess(FormEvent $event) {
        $url = $this->router->generate('tj_main_dashboard_index');
        $event->setResponse(new RedirectResponse($url));
    }

}
