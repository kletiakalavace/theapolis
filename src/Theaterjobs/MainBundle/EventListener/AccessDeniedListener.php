<?php
namespace Theaterjobs\MainBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AccessDeniedListener
 *
 * @category EventListener
 * @package  Theaterjobs\MainBundle\EventListener
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class AccessDeniedListener
{
    protected $router;
    protected $session;
    protected $trans;


    /**
     * Constructor.
     *
     * @param Router $router
     * @param Session $session
     * @param TranslatorInterface $translator
     */
    public function __construct(Router $router, Session $session, TranslatorInterface $translator)
    {
        $this->router  = $router;
        $this->session = $session;
        $this->trans = $translator;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onAccessDeniedException(GetResponseForExceptionEvent $event)
    {
        $msg = $event->getException()->getMessage();

        if (strpos($msg, 'ROLE_MEMBER') !== false) {
            $this->session->getFlashBag()->add('membershipIndex', ['danger' => $this->trans->trans('flash.access.denied')]);
            $route = $this->router->generate('tj_membership_index');
            $event->setResponse(new RedirectResponse($route));
        }
    }
}