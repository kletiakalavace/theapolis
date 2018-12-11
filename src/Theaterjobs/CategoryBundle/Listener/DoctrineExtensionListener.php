<?php

namespace Theaterjobs\CategoryBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * DoctrineExtensionListener for Doctrine Locale
 *
 * @category Entity
 * @package  Theaterjobs\CategoryBundle\EventListener
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @DI\Service("theaterjobs_category.doctrine_extension_listener")
 * @DI\Tag("kernel.event_listener", attributes = {"event" = "kernel.request", "method"="onLateKernelRequest", "priority"="-10"})
 * @DI\Tag("kernel.event_listener", attributes = {"event" = "kernel.request", "method"="onKernelRequest"})
 */
class DoctrineExtensionListener implements ContainerAwareInterface {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @DI\InjectParams({"container"=@DI\Inject("service_container")})
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function onLateKernelRequest(GetResponseEvent $event) {
        $translatable = $this->container->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale($event->getRequest()->getLocale());
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $securityContext = $this->container->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $tokenStorage = $this->container->get('security.token_storage');
        if ($securityContext && $tokenStorage && null !== $tokenStorage->getToken() && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggable = $this->container->get('gedmo.listener.loggable');
            $loggable->setUsername($tokenStorage->getToken()->getUsername());
        }
    }

}
