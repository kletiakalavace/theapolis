<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the admin log.
 *
 * @ORM\Table(name="tj_admin_logs")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @category Entity
 * @package  Theaterjobs\AdminBundle\Entity
 * @author   <Malvin Dake <md@manoolia.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
class AdminLog
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="tj_admin_user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     * @ORM\Column(name="tj_admin_object_id", type="integer")
     */
    private $object;

    /**
     * @var string
     * @ORM\Column(name="tj_admin_mesage", type="string", length=1024, nullable=true)
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(name="tj_admin_action", type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @var \DateTime
     * @ORM\Column(name="action_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $actionAt;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return AdminLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set object
     *
     * @param integer $object
     * @return AdminLog
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return integer
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return AdminLog
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return AdminLog
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set actionAt
     *
     * @param \DateTime $actionAt
     * @return AdminLog
     */
    public function setActionAt($actionAt)
    {
        $this->actionAt = $actionAt;

        return $this;
    }

    /**
     * Get actionAt
     *
     * @return \DateTime
     */
    public function getActionAt()
    {
        return $this->actionAt;
    }
}
