<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for login attempts.
 *
 * @ORM\Table(name="tj_user_login_attempts")
 * @ORM\Entity
 * @category Entity
 * @package  Theaterjobs\UserBundle\Entity
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
class LoginAttempts {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @var string $ipAddress
     * @ORM\Column(name="ip_address", type="string", length=255)
     */
    protected $ipAddress;

    /**
     *
     * @var \Datetime $loginAttemptDate
     * @ORM\Column(name="login_date", type="datetime", nullable=true)
     */
    protected $loginAttemptDate;
    
    /**
     *
     * @var string $loginAttemptMail
     * @ORM\Column(name="e_mail", type="string", length=255, nullable=true)
     */
    protected $loginAttemptMail;


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
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return LoginAttempts
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set loginAttemptDate
     *
     * @param \DateTime $loginAttemptDate
     *
     * @return LoginAttempts
     */
    public function setLoginAttemptDate($loginAttemptDate)
    {
        $this->loginAttemptDate = $loginAttemptDate;

        return $this;
    }

    /**
     * Get loginAttemptDate
     *
     * @return \DateTime
     */
    public function getLoginAttemptDate()
    {
        return $this->loginAttemptDate;
    }

    /**
     * Set loginAttemptMail
     *
     * @param string $loginAttemptMail
     *
     * @return LoginAttempts
     */
    public function setLoginAttemptMail($loginAttemptMail)
    {
        $this->loginAttemptMail = $loginAttemptMail;

        return $this;
    }

    /**
     * Get loginAttemptMail
     *
     * @return string
     */
    public function getLoginAttemptMail()
    {
        return $this->loginAttemptMail;
    }
}
