<?php

namespace Theaterjobs\UserBundle\Entity;

use function Clue\StreamFilter\fun;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\InserateBundle\Model\UserInterface as InserateUser;
use Theaterjobs\MessageBundle\Entity\MessageMetadata;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Model\UserInterface as ProfileUser;
use Theaterjobs\MembershipBundle\Model\UserInterface as MemberUser;
use Theaterjobs\MainBundle\Model\UserInterface as MainUser;
use Theaterjobs\UserBundle\Model\ProfileInterface;
use Theaterjobs\StatsBundle\Model\UserInterface as StatsUser;
use FOS\MessageBundle\Model\ParticipantInterface;

/**
 * Entity for the User.
 *
 * @ORM\Entity(repositoryClass="Theaterjobs\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="tj_user_users")
 * @ORM\HasLifecycleCallbacks()
 * @category Entity
 * @package  Theaterjobs\UserBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class User extends BaseUser implements InserateUser, ProfileUser, MemberUser, MainUser, StatsUser, ParticipantInterface
{
    const ROLE_USER = "ROLE_USER";
    const ROLE_MEMBER = "ROLE_MEMBER";
    const ROLE_ADMIN = "ROLE_ADMIN";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\UserBundle\Model\ProfileInterface", cascade={"persist", "remove"}, inversedBy="user")
     * @ORM\JoinColumn(name="tj_user_profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @ORM\OneToMany(targetEntity="UserOrganization", mappedBy="user")
     */
    protected $userOrganizations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="membership_expires_at", type="date", nullable=true)
     */
    protected $membershipExpiresAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_last_edit_at", type="date", nullable=true)
     */
    protected $passwordLastEditAt;


    /**
     * @var boolean
     *
     * @ORM\Column(name="bankConfirmed", type="boolean")
     */
    protected $bankConfirmed = false;


    /**
     * @ORM\OneToMany(
     *     targetEntity="AuthenticationLogs",
     *     mappedBy="createdBy"
     * )
     */
    protected $userSuccessfulLogs;


    /**
     * @ORM\OneToMany(
     *     targetEntity="Notification",
     *     mappedBy="user",
     *     cascade={"persist"}
     * )
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Notification",
     *     mappedBy="from",
     *     cascade={"persist"}
     * )
     */
    protected $notificationsFrom;

    /**
     * @ORM\OneToMany(
     *     targetEntity="NotificationSettings",
     *     mappedBy="user",
     *     cascade={"persist"}
     * )
     */
    protected $notificationSettings;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UserActivity",
     *     mappedBy="user",
     *     cascade={"persist"}
     * )
     */
    protected $userActivity;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\InserateBundle\Entity\AdminComments",
     *     mappedBy="commentFor",
     *     cascade={"persist"}
     * )
     */
    protected $adminComments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\UserBundle\Entity\AdminUserComments",
     *     mappedBy="admin",
     *     cascade={"persist"}
     * )
     */
    protected $adminUserComments;

    /**
     * @ORM\Column(name="login_counter", type="integer", nullable=true)
     */
    protected $loginCounter;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $online = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="network_last_visit", type="datetime", nullable=true)
     */
    protected $networkLastVisit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="education_last_visit", type="datetime", nullable=true)
     */
    protected $educationLastVisit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="profile_last_visit", type="datetime", nullable=true)
     */
    protected $profileLastVisit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="forum_last_visit", type="datetime", nullable=true)
     */
    protected $forumLastVisit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="job_last_visit", type="datetime", nullable=true)
     */
    protected $jobLastVisit;

    /**
     * @ORM\OneToMany(targetEntity="UserActivity", mappedBy="createdBy", cascade={"persist"})
     */
    protected $uacCreatedBy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="recuring_payment", type="boolean")
     */
    protected $recuringPayment = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="required_recuring_payment_cancel", type="boolean")
     */
    protected $hasRequiredRecuringPaymentCancel = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disabled_deletion_of_account", type="boolean")
     */
    protected $disabledDeleteAccount = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="extend_membership", type="boolean")
     */
    protected $extendMembership = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="quit_contract", type="boolean")
     */
    protected $quitContract = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\AdminBundle\Entity\SepaXmlBilling",
     *     mappedBy="lastDownloadedBy",
     *     cascade={"persist"}
     * )
     */
    protected $sepaXmlBillings;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="quit_contract_date", type="datetime", nullable=true)
     */
    protected $quitContractDate;

    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }


    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\UserBundle\Entity\NameChangeRequest",
     *     mappedBy="createdBy",
     *     cascade={"persist"}
     * )
     */
    protected $userNameChangeRequests;


    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\UserBundle\Entity\NameChangeRequest",
     *     mappedBy="updatedBy",
     *     cascade={"persist"}
     * )
     */
    protected $userManagedNameChangeRequests;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\Inserate", mappedBy="user")
     */
    protected $inserates;

    /**
     * @var boolean
     * @ORM\Column(name="has_notifications", type="boolean")
     */
    protected $hasNotifications = false;

    /**
     * One Category has One Vio.
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\TeamMembershipApplication", mappedBy="user")
     */
    private $membershipApplications;

    /**
     * One user has many threads
     * @var MessageMetadata
     * @ORM\OneToMany(targetEntity="Theaterjobs\MessageBundle\Entity\ThreadMetadata", mappedBy="participant")
     */
    protected $metadataThreads;

    /**
     * @return mixed
     */
    public function getInserates()
    {
        return $this->inserates;
    }

    /**
     * @param $inserates
     */
    protected function setInserates($inserates)
    {
        $this->inserates = $inserates;
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

    /**
     * Get Profile
     *
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set Profile
     *
     * @param ProfileInterface $profile
     */
    public function setProfile(ProfileInterface $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Set membershipExpiresAt
     *
     * @param \DateTime $membershipExpiresAt
     * @return User
     */
    public function setMembershipExpiresAt($membershipExpiresAt)
    {
        if ($membershipExpiresAt !== null)
            $this->membershipExpiresAt = clone $membershipExpiresAt;
        else
            $this->membershipExpiresAt = $membershipExpiresAt;
        return $this;
    }

    /**
     * Get membershipExpiresAt
     *
     * @return \DateTime
     */
    public function getMembershipExpiresAt()
    {
        if ($this->membershipExpiresAt !== null)
            return clone $this->membershipExpiresAt;
        else
            return $this->membershipExpiresAt;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->userOrganizations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adminComments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->metadataThreads = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add userOrganizations
     *
     * @param \Theaterjobs\UserBundle\Entity\UserOrganization $userOrganization
     * @return User
     */
    public function addUserOrganization(\Theaterjobs\UserBundle\Entity\UserOrganization $userOrganization)
    {
        $userOrganization->setUser($this);
        $this->userOrganizations[] = $userOrganization;

        return $this;
    }

    /**
     * Remove userOrganizations
     *
     * @param \Theaterjobs\UserBundle\Entity\UserOrganization $userOrganizations
     */
    public function removeUserOrganization(\Theaterjobs\UserBundle\Entity\UserOrganization $userOrganizations)
    {
        $this->userOrganizations->removeElement($userOrganizations);
    }

    /**
     * Get userOrganizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserOrganizations()
    {
        return $this->userOrganizations;
    }

    /**
     * Add notifications
     *
     * @param \Theaterjobs\UserBundle\Entity\Notification $notifications
     * @return User
     */
    public function addNotification(\Theaterjobs\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Theaterjobs\UserBundle\Entity\Notification $notifications
     */
    public function removeNotification(\Theaterjobs\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add notificationSettings
     *
     * @param \Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings
     * @return User
     */
    public function addNotificationSetting(\Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings)
    {
        $this->notificationSettings[] = $notificationSettings;

        return $this;
    }

    /**
     * Remove notificationSettings
     *
     * @param \Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings
     */
    public function removeNotificationSetting(\Theaterjobs\UserBundle\Entity\NotificationSettings $notificationSettings)
    {
        $this->notificationSettings->removeElement($notificationSettings);
    }

    /**
     * Get notificationSettings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotificationSettings()
    {
        return $this->notificationSettings;
    }

    /**
     * Add userActivity
     *
     * @param \Theaterjobs\UserBundle\Entity\UserActivity $userActivity
     * @return Organization
     */
    public function addUserActivity(\Theaterjobs\UserBundle\Entity\UserActivity $userActivity)
    {
        $this->userActivity[] = $userActivity;

        return $this;
    }

    /**
     * Remove userActivity
     *
     * @param \Theaterjobs\UserBundle\Entity\UserActivity $userActivity
     */
    public function removeUserActivity(\Theaterjobs\UserBundle\Entity\UserActivity $userActivity)
    {
        $this->userActivity->removeElement($userActivity);
    }

    /**
     * Get userActivity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserActivity()
    {
        return $this->userActivity;
    }

    /**
     * Get jobLastVisit
     *
     * @return \DateTime
     */
    function getJobLastVisit()
    {
        return $this->jobLastVisit;
    }

    /**
     * Set lastVisitOnDashboard
     *
     * @param \DateTime $jobLastVisit
     * @return User
     */
    function setJobLastVisit($jobLastVisit)
    {
        $this->jobLastVisit = $jobLastVisit;
        return $this;
    }

    function getNetworkLastVisit()
    {
        return $this->networkLastVisit;
    }

    function getEducationLastVisit()
    {
        return $this->educationLastVisit;
    }

    function getProfileLastVisit()
    {
        return $this->profileLastVisit;
    }

    function getForumLastVisit()
    {
        return $this->forumLastVisit;
    }

    function setNetworkLastVisit($networkLastVisit)
    {
        $this->networkLastVisit = $networkLastVisit;
    }

    function setEducationLastVisit($educationLastVisit)
    {
        $this->educationLastVisit = $educationLastVisit;
    }

    function setProfileLastVisit($profileLastVisit)
    {
        $this->profileLastVisit = $profileLastVisit;
    }

    function setForumLastVisit($forumLastVisit)
    {
        $this->forumLastVisit = $forumLastVisit;
    }

    function getUacCreatedBy()
    {
        return $this->uacCreatedBy;
    }

    function setUacCreatedBy($uacCreatedBy)
    {
        $this->uacCreatedBy = $uacCreatedBy;
    }

    function getNotificationsFrom()
    {
        return $this->notificationsFrom;
    }

    function setNotificationsFrom($notificationsFrom)
    {
        $this->notificationsFrom = $notificationsFrom;
    }

    /**
     * Add adminComment
     *
     * @param \Theaterjobs\UserBundle\Entity\AdminComments $adminComment
     *
     * @return User
     */
    public function addAdminComment(\Theaterjobs\InserateBundle\Entity\AdminComments $adminComment)
    {
        $this->adminComments[] = $adminComment;

        return $this;
    }

    /**
     * Remove adminComment
     *
     * @param \Theaterjobs\UserBundle\Entity\AdminComments $adminComment
     */
    public function removeAdminComment(\Theaterjobs\InserateBundle\Entity\AdminComments $adminComment)
    {
        $this->adminComments->removeElement($adminComment);
    }

    /**
     * Get adminComments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdminComments()
    {
        return $this->adminComments;
    }

    /**
     * Set bankConfirmed
     *
     * @param boolean $bankConfirmed
     *
     * @return User
     */
    public function setBankConfirmed($bankConfirmed)
    {
        $this->bankConfirmed = $bankConfirmed;

        return $this;
    }

    /**
     * Get bankConfirmed
     *
     * @return boolean
     */
    public function getBankConfirmed()
    {
        return $this->bankConfirmed;
    }

    /**
     * Set recuringPayment
     *
     * @param boolean $recuringPayment
     *
     * @return User
     */
    public function setRecuringPayment($recuringPayment)
    {
        $this->recuringPayment = $recuringPayment;

        return $this;
    }

    /**
     * Get recuringPayment
     *
     * @return boolean
     */
    public function getRecuringPayment()
    {
        return $this->recuringPayment;
    }

    function getHasRequiredRecuringPaymentCancel()
    {
        return $this->hasRequiredRecuringPaymentCancel;
    }

    function setHasRequiredRecuringPaymentCancel($hasRequiredRecuringPaymentCancel)
    {
        $this->hasRequiredRecuringPaymentCancel = $hasRequiredRecuringPaymentCancel;
    }

    /**
     * Set disabledDeleteAccount
     *
     * @param boolean $disabledDeleteAccount
     *
     * @return User
     */
    public function setDisabledDeleteAccount($disabledDeleteAccount)
    {
        $this->disabledDeleteAccount = $disabledDeleteAccount;

        return $this;
    }

    /**
     * Get disabledDeleteAccount
     *
     * @return boolean
     */
    public function getDisabledDeleteAccount()
    {
        return $this->disabledDeleteAccount;
    }

    function getExtendMembership()
    {
        return $this->extendMembership;
    }

    function setExtendMembership($extendMembership)
    {
        $this->extendMembership = $extendMembership;
    }

    function getQuitContract()
    {
        return $this->quitContract;
    }

    function setQuitContract($quitContract)
    {
        $this->quitContract = $quitContract;
    }

    function getQuitContractDate()
    {
        return $this->quitContractDate;
    }

    function setQuitContractDate($quitContractDate)
    {
        $this->quitContractDate = $quitContractDate;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordLastEditAt()
    {
        return $this->passwordLastEditAt;
    }

    /**
     * @param \DateTime $passwordLastEditAt
     * @return User
     */
    public function setPasswordLastEditAt($passwordLastEditAt)
    {
        $this->passwordLastEditAt = $passwordLastEditAt;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getUserNameChangeRequests()
    {
        return $this->userNameChangeRequests;
    }


    /**
     * @return mixed
     */
    public function getUserManagedNameChangeRequests()
    {
        return $this->userManagedNameChangeRequests;
    }


    /**
     * @return int
     */
    public function getLoginCounter()
    {
        return $this->loginCounter;
    }

    /**
     * @param int $loginCounter
     * @return User
     */
    public function setLoginCounter($loginCounter)
    {
        $this->loginCounter = $loginCounter;
        return $this;
    }


    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * @param bool $online
     * @return User
     */
    public function setOnline($online)
    {
        $this->online = $online;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserSuccessfulLogs()
    {
        return $this->userSuccessfulLogs;
    }

    /**
     * @param mixed $userSuccessfulLogs
     * @return User
     */
    public function setUserSuccessfulLogs($userSuccessfulLogs)
    {
        $this->userSuccessfulLogs = $userSuccessfulLogs;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasNotifications()
    {
        return $this->hasNotifications;
    }

    /**
     * @param $hasNotifications
     * @internal param bool $unseenNotifications
     */
    public function setHasNotifications($hasNotifications)
    {
        $this->hasNotifications = $hasNotifications;
    }

    /**
     * @return mixed
     */
    public function getMembershipApplications()
    {
        return $this->membershipApplications;
    }

    /**
     * @param mixed $membershipApplications
     * @return User
     */
    public function setMembershipApplications($membershipApplications)
    {
        $this->membershipApplications = $membershipApplications;
        return $this;
    }

    /**
     * @return MessageMetadata
     */
    public function getMetadataThreads()
    {
        return $this->metadataThreads;
    }

    /**
     * @param MessageMetadata $metadataThreads
     */
    public function setMetadataThreads($metadataThreads)
    {
        $this->metadataThreads = $metadataThreads;
    }

    /**
     * Override method set email (since we are using email as username set the username same as email)
     * @param $email
     * @return BaseUser|\FOS\UserBundle\Model\UserInterface|void
     */
    public function setEmail($email)
    {
        // call the parent function to set the email
        parent::setEmail($email);

        if (is_null($this->username)) {
            // set the username same as email
            $this->setUsername($email);
        }
    }

    /**
     * @param $organization
     * @return boolean
     */
    public function isTeamMember(Organization $organization = null)
    {
        // Check if user is team member of this organization
        if ($organization) {
            return $this->userOrganizations->exists(function ($key, $element) use ($organization) {
                $isPartOf = $element->getOrganization()->getId() == $organization->getId();
                return $isPartOf && !$element->getRevokedAt() && $element->getGrantedAt();
            });
        }
        // Check if user is just part of some organization
        return $this->userOrganizations->exists(function ($key, $element) {
            return !$element->getRevokedAt() && $element->getGrantedAt();
        });
    }

    /**
     * Get user organization ids
     * @return mixed
     */
    public function getUserOrganizationIds()
    {
        return array_reduce($this->jobFavourite->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }

    /**
     * check the session user is the owner of this entity
     *
     * @param User $user
     * @return bool
     */
    public function isEqual(User $user)
    {
        return $this->id === $user->getId();
    }

}
