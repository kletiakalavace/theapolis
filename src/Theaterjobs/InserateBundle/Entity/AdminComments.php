<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Vacancy
 *
 * @ORM\Table("tj_inserate_admin_comments")
 * @ORM\Entity
 */
class AdminComments {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="adminComments", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_inserate_organizations_id", referencedColumnName="id", nullable=true)
     */
    protected $organization;
    
    /**
     * @ORM\ManyToOne(targetEntity="Inserate", inversedBy="adminComments", fetch="EAGER",cascade={"persist"})
     * @ORM\JoinColumn(name="tj_inserate_inserate_id", referencedColumnName="id", nullable=true)
     */
    protected $inserate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $publishedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_inserate_users_id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", inversedBy="adminComments", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_inserate_admincomments_for_user_id", referencedColumnName="id")
     */
    protected $commentFor;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return AdminComments
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     * @return AdminComments
     */
    public function setPublishedAt($publishedAt) {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt() {
        return $this->publishedAt;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     * @return AdminComments
     */
    public function setArchivedAt($archivedAt) {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt() {
        return $this->archivedAt;
    }


    /**
     * Set organization
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organization
     * @return AdminComments
     */
    public function setOrganization(\Theaterjobs\InserateBundle\Entity\Organization $organization = null) {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     */
    public function getOrganization() {
        return $this->organization;
    }

    /**
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     * @return AdminComments
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Set organization
     *
     * @param \Theaterjobs\InserateBundle\Entity\Inserate $inserate
     * @return AdminComments
     */
    public function setInserate(\Theaterjobs\InserateBundle\Entity\Inserate $inserate = null) {
        $this->inserate = $inserate;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Theaterjobs\InserateBundle\Entity\Inserate
     */
    public function getInserate() {
        return $this->inserate;
    }

    /**
     * Set commentFor
     *
     * @param \Theaterjobs\InserateBundle\Entity\User $commentFor
     *
     * @return AdminComments
     */
    public function setCommentFor(\Theaterjobs\UserBundle\Entity\User $commentFor = null)
    {
        $this->commentFor = $commentFor;

        return $this;
    }

    /**
     * Get commentFor
     *
     * @return \Theaterjobs\InserateBundle\Entity\User
     */
    public function getCommentFor()
    {
        return $this->commentFor;
    }
}
