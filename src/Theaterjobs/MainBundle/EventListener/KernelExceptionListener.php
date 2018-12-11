<?php

namespace Theaterjobs\MainBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * KernelExceptionListener
 *
 * @category EventListener
 * @package  Theaterjobs\MainBundle\EventListener
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class KernelExceptionListener {

    protected $templating;
    protected $container;

    /**
     * Constructor
     *
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, TwigEngine $templating) {
        $this->container = $container;
        $this->templating = $templating;
    }

    /**
     * Get a kernel exeption.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) {
        // You get the exception object from the received event
        $exception = $event->getException();
        $request = $event->getRequest();
        $response = $event->getResponse();
        $environment = $this->container->get('kernel')->getEnvironment();
        if(!($locale = $request->attributes->get('_locale'))){
            $locale = $this->container->getParameter('locale');
        }

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            if ($exception->getStatusCode() == 404) {
                $route = $request->attributes->get('_route');
                if ($route == 'fos_user_registration_confirm') {
                    $response = new Response($this->templating->render('TheaterjobsUserBundle:Registration:error404confirm.html.twig', array('exception' => $exception)));
                } else {
                    $response = new Response(
                            $this->templating->render(
                                    'TheaterjobsMainBundle:Default:error404.html.twig', array(
                                        'exception' => $exception,
                                        'locale' => $locale
                                    )
                            )
                    );
                }

                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else if ($exception->getStatusCode() == 403) {
                $response = new Response(
                        $this->templating->render(
                                'TheaterjobsMainBundle:Default:error403.html.twig', array(
                            'exception' => $exception,
                                'locale' => $locale
                                )
                        )
                );
            }
        } else {
            if ($environment != 'dev') {
                $response = new Response(
                        $this->templating->render(
                                'TheaterjobsMainBundle:Default:error500.html.twig', array(
                            'exception' => $exception,
                                'locale' => $locale
                                )
                        )
                );
            }
        }
        if ($response) {
            $event->setResponse($response);
        }
    }

}
