<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the User.
 *
 * @ORM\Entity()
 * @ORM\Table(name="tj_user_friendship",
 *              uniqueConstraints={@ORM\UniqueConstraint(name="user_unique",
 *                               columns={"user1_id", "user2_id"})}
 *            )
 * @ORM\HasLifecycleCallbacks()
 * @category Entity
 * @package  Theaterjobs\UserBundle\Entity
 */
class Friendship {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user1_id",referencedColumnName="id")
     */
    protected $user1;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user2_id",referencedColumnName="id")
     */
    protected $user2;
    
    /**
     * @ORM\Column(type="integer",name="status")
     */
    protected $status;

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
     * Set status
     *
     * @param integer $status
     * @return Friendship
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user1
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user1
     * @return Friendship
     */
    public function setUser1(\Theaterjobs\UserBundle\Entity\User $user1 = null)
    {
        $this->user1 = $user1;

        return $this;
    }

    /**
     * Get user1
     *
     * @return \Theaterjobs\UserBundle\Entity\User 
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * Set user2
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user2
     * @return Friendship
     */
    public function setUser2(\Theaterjobs\UserBundle\Entity\User $user2 = null)
    {
        $this->user2 = $user2;

        return $this;
    }

    /**
     * Get user2
     *
     * @return \Theaterjobs\UserBundle\Entity\User 
     */
    public function getUser2()
    {
        return $this->user2;
    }
}
