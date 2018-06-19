<?php

namespace Theaterjobs\MembershipBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Theaterjobs\MembershipBundle\Model\UserInterface;

/**
 * Description of MembershipExpiredEvent
 *
 * @category Event
 * @package  Theaterjobs\MembershipBundle\Event
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class MembershipExpiredEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;
    /**
     * @var bool
     */
    protected $flush = true;


    protected $queue = "cron";

    /**
     * @param UserInterface
     */
    public function __construct(UserInterface $user) {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isFlush()
    {
        return $this->flush;
    }

    /**
     * @param bool $flush
     */
    public function setFlush($flush)
    {
        $this->flush = $flush;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }
}
