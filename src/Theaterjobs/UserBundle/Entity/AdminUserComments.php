<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * AdminUserComments
 *
 * @ORM\Table(name="tj_admin_user_comments")
 * @ORM\Entity(repositoryClass="Theaterjobs\UserBundle\Entity\AdminUserCommentsRepository")
 */
class AdminUserComments
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="tj_user_users_id")
     */
    protected $user;

    /**
     * @var User $admin
     *
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", inversedBy="adminUserComments", fetch="LAZY")
     * @ORM\JoinColumn(name="tj_user_admin_id")
     */
    protected $admin;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;


    /**
     * @var DateTime $publishedAt
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $publishedAt;

    /**
     * @var DateTime $archivedAt
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    protected $archivedAt;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param User $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @param DateTime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param DateTime $archivedAt
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


}

