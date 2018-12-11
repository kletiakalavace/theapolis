<?php

namespace Theaterjobs\StatsBundle\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\StatsBundle\Event\ViewEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Theaterjobs\StatsBundle\DependencyInjection\BotChecker;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\StatsEvents;
use JMS\DiExtraBundle\Annotation as DI;
use \Symfony\Component\HttpFoundation\Session\Session;

/**
 * StatsSubscriber
 *
 * @category EventListener
 * @package  Theaterjobs\StatsBundle\EventListener
 * @author   Jurgen Rexhmati <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_stats.statslistener")
 */
class StatsListener
{
    /**
     * @var AuthorizationChecker;
     */
    private $securityContext;

    /**
     * @var BotChecker
     */
    private $botChecker;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Session
     */
    private $session;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.authorization_checker"),
     *     "botChecker" = @DI\Inject("helper.botchecker"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "session" = @DI\Inject("session")
     * })
     * @param AuthorizationChecker $securityContext
     * @param BotChecker $botChecker
     * @param EntityManager $em
     * @param RequestStack $requestStack
     * @param Session $session
     */
    public function __construct(AuthorizationChecker $securityContext, BotChecker $botChecker, EntityManager $em, RequestStack $requestStack, Session $session)
    {
        $this->securityContext = $securityContext;
        $this->botChecker = $botChecker;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    /**
     * @DI\Observe(StatsEvents::STATS_VIEW, priority = 255)
     * @param ViewEvent $event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onStatsView(ViewEvent $event)
    {
        if ($this->securityContext->isGranted('ROLE_ADMIN') || !$this->botChecker->isBot()) {
            return;
        }
        $user = $event->getUser();

        $view = new View();
        $view->setForeignKey($event->getFK());
        $view->setIP($this->requestStack->getCurrentRequest()->getClientIp());
        $view->setObjectClass(get_class($event->getClassName()));

        if ($event->getClassName() === Profile::class) {
            if ($user && $user->getProfile()->getDoNotTrackViews() && $event->isDoNotTrack()) {
                $view->setUser($user);
            } else {
                $view->setUser(null);
            }
        }

        $this->em->persist($view);
        $this->em->flush();
    }
}
