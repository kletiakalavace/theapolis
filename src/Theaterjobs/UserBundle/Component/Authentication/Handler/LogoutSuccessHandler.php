<?php
namespace Theaterjobs\UserBundle\Component\Authentication\Handler;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * LogoutSuccessHandler
 *
 * @category Handler
 * @package  Theaterjobs\UserBundle\Component\Authentication\Handler
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $router;

    /**
     * Constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Overriding this method.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onLogoutSuccess(Request $request)
    {
//         $session = $request->getSession();

//         if ($session->has('referer')) {
//             if ($session->get('referer') !== null
//             && $session->get('referer') !== '')
//             {
//                 $response = new RedirectResponse($session->get('referer'));
//             } else {
//                 $response = new RedirectResponse($request->getBasePath() . '/');
//             }
//         } else {
//             // if no referer then go to homepage
//             $response = new RedirectResponse($request->getBasePath() . '/');
//         }

//         if ($request->isXmlHttpRequest() || $request->request->get('_format') === 'json') {
//             $response = new Response(json_encode(array('status' => 'success')));
//             $response->headers->set('Content-Type', 'application/json');
//         }

        return new RedirectResponse(
            $this->router->generate('tj_main_default_goodby')
        );
    }
}