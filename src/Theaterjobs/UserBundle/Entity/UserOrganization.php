<?php

namespace Theaterjobs\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Model\OrganizationInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Theaterjobs\InserateBundle\Model\UserOrganizationInterface as InserateUserOrganization;

/**
 * Entity for the UserOrganization
 *
 * @ORM\Entity(repositoryClass="Theaterjobs\UserBundle\Entity\UserOrganizationRepository")
 * @ORM\Table(
 *  name="tj_user_users_organizations",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(
 *          name="tj_user_users_organizations_idx", columns={"tj_user_users_id", "tj_user_organizations_id"}
 *          )
 *      }
 *  )
 *
 *
 * @category Entity
 * @package  Theaterjobs\UserBundle\Entity
 * @author   Vilson Duka <vilsondev@gmail.com>
 * @author   Malvin Dake <malvin2007@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class UserOrganization implements InserateUserOrganization {

    const TEAM_MEMBER_LIMIT = 15;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userOrganizations")
     * @ORM\JoinColumn(name="tj_user_users_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Model\OrganizationInterface", inversedBy="userOrganizations")
     * @ORM\JoinColumn( name="tj_user_organizations_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $organization;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="requested_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $requestedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="granted_at", type="datetime", nullable=true)
     */
    protected $grantedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="revoked_at", type="datetime", nullable=true)
     */
    protected $revokedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_comment", type="string", length=255, nullable=true)
     */
    protected $adminComment;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set requestedAt
     *
     * @param \DateTime $requestedAt
     * @return UserOrganization
     */
    public function setRequestedAt($requestedAt) {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    /**
     * Get requestedAt
     *
     * @return \DateTime
     */
    public function getRequestedAt() {
        return $this->requestedAt;
    }

    /**
     * Set grantedAt
     *
     * @param \DateTime $grantedAt
     * @return UserOrganization
     */
    public function setGrantedAt($grantedAt) {
        $this->grantedAt = $grantedAt;

        return $this;
    }

    /**
     * Get grantedAt
     *
     * @return \DateTime
     */
    public function getGrantedAt() {
        return $this->grantedAt;
    }


    /**
     * Set revokedAt
     *
     * @param \DateTime $revokedAt
     * @return UserOrganization
     */
    public function setRevokedAt($revokedAt) {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Get revokedAt
     *
     * @return \DateTime
     */
    public function getRevokedAt() {
        return $this->revokedAt;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return UserOrganization
     */
    public function setConfirmed($confirmed) {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed() {
        return $this->confirmed;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserOrganization
     */
    public function setUser(User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set organization
     *
     * @param OrganizationInterface $organization
     * @return UserOrganization
     */
    public function setOrganization(OrganizationInterface $organization = null) {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get Organization
     *
     * @return Organization
     */
    public function getOrganization() {
        return $this->organization;
    }

    /**
     * Get adminComment
     *
     * @return string
     */
    public function getAdminComment() {
        return $this->adminComment;
    }

    /**
     * Set adminComment
     *
     * @param string $adminComment
     * @return adminComment
     */
    public function setAdminComment($adminComment) {
        $this->adminComment = $adminComment;
    }

    /**
     * Set newOrganization
     *
     * @param boolean $newOrganization
     * @return UserOrganization
     */
    public function setNewOrganization($newOrganization) {
        $this->newOrganization = $newOrganization;

        return $this;
    }

    /**
     * Get newOrganization
     *
     * @return boolean
     */
    public function getNewOrganization() {
        return $this->newOrganization;
    }

}
