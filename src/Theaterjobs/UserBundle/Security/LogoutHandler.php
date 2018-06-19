<?php
namespace Theaterjobs\UserBundle\Security;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Do post logout stuff
 * @DI\Service("theaterjobs_user.security.logout_handler")
 */




class LogoutHandler implements LogoutHandlerInterface
{
    private $router;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
   
    /**
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $authToken
     * @return RedirectResponse
     */
    public function logout(Request $request, Response $response, TokenInterface $authToken)
    {
        $user = $authToken->getUser();
        $user->setOnline(false);
        $this->em->persist($user);
        $this->em->flush();
    }
}