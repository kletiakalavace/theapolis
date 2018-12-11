<?php

namespace Theaterjobs\MembershipBundle\EventListener;

use Carbon\Carbon;
use Theaterjobs\MembershipBundle\MembershipEvents;
use Theaterjobs\MembershipBundle\Event\MembershipExpiredEvent;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\UserBundle\Entity\User;
use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * MembershipExpiredListener
 *
 * @category EventListener
 * @package  Theaterjobs\StatsBundle\EventListener
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_user.membership_expires_listener")
 */
class MembershipExpiredListener {

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     *
     * @var EntityManager
     */
    private $em;

    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("fos_user.user_manager"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager, EntityManager $entityManager) {
        $this->userManager = $userManager;
        $this->em = $entityManager;
    }

    /**
     * @DI\Observe(MembershipEvents::MEMBERSHIP_EXPIRED)
     * @param MembershipExpiredEvent $event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onMembershipExpired(MembershipExpiredEvent $event) {
        $user = $event->getUser();
        $this->resetFlags($user);

        //Archive user Educations
        $archiveEducations = new JobQueue('app:archive:educations', [$user->getId(),1], true, $event->getQueue());
        $this->em->persist($archiveEducations);

        //Delete Save searches
        $saveSearchDelete = new JobQueue('app:delete:save-searches', [$user->getProfile()->getId()], true, $event->getQueue());
        $this->em->persist($saveSearchDelete);

        if ($event->isFlush()) {
            $this->em->flush();
        }
    }

    /**
     * Reset data of user related with membership
     *
     * @param User $user
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function resetFlags(User $user)
    {
        if ($user->hasRole('ROLE_MEMBER')) {
            $user->removeRole('ROLE_MEMBER');
            $user->addRole('ROLE_USER');
        }

        $user->setQuitContract(true);
        $user->setExtendMembership(false);
        $user->setMembershipExpiresAt(null);
        $user->setQuitContractDate(Carbon::now());
        $user->setHasRequiredRecuringPaymentCancel(false);
    }

}
