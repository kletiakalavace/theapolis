<?php

namespace Theaterjobs\UserBundle\Component\Authentication\Handler;

use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Theaterjobs\UserBundle\Entity\LoginAttempts;
use Theaterjobs\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AuthenticationHandler
 *
 * @category Handler
 * @package  Theaterjobs\UserBundle\Component\Authentication\Handler
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_user.component.authentication.handler")
 */
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{

    private $router;
    private $em;
    private $mailer;
    private $container;
    private $userManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router")
     * })
     */

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */

    /**
     * @DI\InjectParams({
     *     "tran" = @DI\Inject("translator")
     * })
     */

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     * @param Router $router
     * @param \Swift_Mailer $mailer
     * @param ContainerInterface $container
     * @param TranslatorInterface $translator
     */


    public function __construct(Router $router, \Swift_Mailer $mailer, ContainerInterface $container, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->userPoolEm = $container->get('doctrine.orm.user_pool_entity_manager');
        $this->userManager = $container->get('fos_user.user_manager');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $username = $request->request->get('_username');

        $exists = $this->em->getRepository('TheaterjobsUserBundle:EmailBlacklist')
            ->findOneBy(
                array('email' => $username)
            );
        //User email is banned
        if ($exists) {

            $result = [
                'success' => false,
                'message' => "login.notPossible"
            ];
            $response = new JsonResponse($result);

            $this->logoutUser($response);
            $request->request->remove('_remember_me');

            return $response;
        }

        if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
            $url = $targetPath;
        } else {
            // Otherwise, redirect him to wherever you want
            $url = $this->router->generate('tj_main_default_home');
        }

        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
            $response = new JsonResponse(['success' => true, 'targetUrl' => $url]);

        } else {
            // If the user tried to access a protected resource and was forces to login
            // redirect him back to that resource
            $response = new RedirectResponse($url);
        }
        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $attempts = null;
        $reset = false;
        $liveMode = $this->container->getParameter('livemode');
        $username = $request->request->get('_username');

        if ($liveMode === 1) {
            syslog(LOG_ALERT, 'Im on live mode');
            $sql = "SELECT sha1_password, salt, logged_in_successful from users where email = '" . $username . "' LIMIT 1";
            $stmt = $this->userPoolEm->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $this->userPoolEm->close();

            if ($result !== false) {
                syslog(LOG_ALERT, 'result exits');
                foreach ($result as $row) {
                    if ($row['logged_in_successful'] == false)  {
                        syslog(LOG_ALERT, 'never logged in before');
                        $salt = $row['salt'];
                        $sha1Password = sha1($salt . $request->request->get('_password'));
                        if ($sha1Password == $row['sha1_password']) {
                            syslog(LOG_ALERT, 'password is correct');
                            $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $username));
                            $user->setPlainPassword($request->request->get('_password'));
                            $user->setEnabled(true);
                            $this->userManager->updateUser($user);
                            syslog(LOG_ALERT, 'old password is set as default password in the new system');
                            $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
                            // For older versions of Symfony, use security.context here
                            $this->container->get("security.token_storage")->setToken($token);
                            // Fire the login event
                            // Logging the user in above the way we do it doesn't do this automatically
                            $event = new InteractiveLoginEvent($request, $token);
                            $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                            syslog(LOG_ALERT, 'interactive login successfull');

                            // update old database flag, user has logged in successful in the new system
                            $sql = "UPDATE users SET logged_in_successful = 1 where email = '" . $username . "'";
                            $stmt = $this->userPoolEm->getConnection()->prepare($sql);
                            $stmt->execute();
                            $this->userPoolEm->close();

                            $url = $this->router->generate('tj_main_dashboard_index');
                            syslog(LOG_ALERT, 'rediretion to the dashboard');
                            return new RedirectResponse($url);
                        }
                    }
                }
            }
        }

        //If user has to confirm the confirmation token
        if ($exception instanceof DisabledException) {
            $result = [
                'success' => false,
                'message' => $this->translator->trans("flash.user.login.confirm_token.first", [], 'flashes'),
                'reset' => false,
                'disabled' => true
            ];
        } else {

            $loginAttempt = new LoginAttempts();
            $loginAttempt->setLoginAttemptDate(new \DateTime());
            $loginAttempt->setIpAddress($request->getClientIp());
            $loginAttempt->setLoginAttemptMail($request->request->get("_username"));

            $this->em->persist($loginAttempt);
            $this->em->flush();
            $date = date("Y-m-d H:i:s");
            $time = strtotime($date);
            $time = $time - (16 * 60);
            $date = date("Y-m-d H:i:s", $time);

            $qb = $this->em->createQueryBuilder();
            $loginAttemptsFromIp = $qb->select('count(attempt.id)')
                ->from('TheaterjobsUserBundle:LoginAttempts', 'attempt')
                ->where('attempt.ipAddress= :ip')
                ->andWhere('attempt.loginAttemptDate >= :date')
                ->setParameters(array('ip' => $request->getClientIp(), 'date' => $date))
                ->getQuery()->getSingleResult();
            if ($loginAttemptsFromIp[1] < 3) {
                $url = $this->router->generate('fos_user_security_login');
            } elseif ($loginAttemptsFromIp[1] == 3) {
                $reset = true;
                $url = $this->router->generate('fos_user_resetting_request');
            } else {
                $entity = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('username' => $loginAttempt->getLoginAttemptMail()));

                if ($entity) {
                    $this->sendMultipleFailedLoginEmail($entity);
                }

                $attempts = $this->translator->trans(
                    "flash.user.login.many.attempts", [], 'flashes');
            }
            $result = [
                'success' => false,
                'message' => $attempts ? $attempts : $this->translator->trans("flash.user.login.bad.credentials", [], 'flashes'),
                'reset' => $reset
            ];

        }
        $url = $this->router->generate('fos_user_security_login');
        return $request->isXmlHttpRequest() ? new JsonResponse($result) : new RedirectResponse($url);
    }

    /** Send email for multiple failed login attemts.
     *
     * @param $user
     * @throws \Twig_Error
     */
    private function sendMultipleFailedLoginEmail($user)
    {
        //Email content
        $emailContent = $this->container->get('templating')->render('TheaterjobsUserBundle:Security:multipleFailedLoginAttemptsEmail.html.twig', [
            'name' => $user->getProfile()->getSubtitle()
        ]);

        $this->container->get('base_mailer')
            ->sendEmailMessage(
                $this->container->get('translator')->trans('loginfailed.email.subject', array(), 'emails'),
                $emailContent,
                $this->container->getParameter('noreply_email'),
                $user->getEmail(),
                'text/html'
            );
    }

    /**
     * Logs out manually the user
     *
     * @param JsonResponse $response
     *
     * @return Response $response
     */
    public function logoutUser(&$response)
    {
        // Logging user out.
        $this->container->get('security.token_storage')->setToken(null);

        // Invalidating the session.
        $session = $this->container->get('request')->getSession();
        $session->invalidate();
    }

}
