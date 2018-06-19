<?php

namespace Theaterjobs\CategoryBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * DoctrineExtensionListener for Doctrine Locale
 *
 * @category Entity
 * @package  Theaterjobs\CategoryBundle\EventListener
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_category.locale_listener")
 * @DI\Tag("kernel.event_subscriber")
 */
class LocaleListener implements EventSubscriberInterface {

    private $defaultLocale;

    /**
     * @DI\InjectParams({"defaultLocale" = @DI\Inject("%locale%")})
     * @param type $defaultLocale
     */
    public function __construct($defaultLocale = 'en') {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        $locale = $request->attributes->get('_locale');
        if ($locale) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    /*
     * (non-PHPdoc) @see \Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */

    public static function getSubscribedEvents() {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }

}
