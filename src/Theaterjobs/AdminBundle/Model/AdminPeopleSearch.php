<?php

namespace Theaterjobs\AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * The AdminPeopleType Model
 *
 * This object will contain various properties which will be mapped to our filters, sort and pagination cafeterias.
 *
 * @category Model
 * @package  Theaterjobs\AdminBundle\Model
 * @author   Igli Hoxha <igliihoxha@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theapolis.de
 */
class AdminPeopleSearch
{
    protected $profileRegistration;

    protected $user;

    protected $input;

    protected $userLastLogin;

    protected $userEmail;

    protected $choices;

    protected $order;

    protected $orderCol;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return AdminPeopleSearch
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderCol()
    {
        return $this->orderCol;
    }

    /**
     * @param mixed $orderCol
     * @return AdminPeopleSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param mixed $choices
     * @return AdminPeopleSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfileRegistration()
    {
        return $this->profileRegistration;
    }

    /**
     * @param mixed $profileRegistration
     * @return AdminPeopleSearch
     */
    public function setProfileRegistration($profileRegistration)
    {
        $this->profileRegistration = $profileRegistration;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserLastLogin()
    {
        return $this->userLastLogin;
    }

    /**
     * @param mixed $userLastLogin
     * @return AdminPeopleSearch
     */
    public function setUserLastLogin($userLastLogin)
    {
        $this->userLastLogin = $userLastLogin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @param mixed $userEmail
     * @return AdminPeopleSearch
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     * @return AdminPeopleSearch
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return AdminPeopleSearch
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
