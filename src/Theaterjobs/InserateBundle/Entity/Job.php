<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Theaterjobs\InserateBundle\Model\QualificationInterface;
use Theaterjobs\InserateBundle\Model\UserInterface;
use Theaterjobs\InserateBundle\Model\ProfileInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Model\JobInterface as ProfileJob;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Entity for the job.
 *
 * @ORM\Table(name="tj_inserate_jobs")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\JobRepository"
 * )
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 *
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link     http://www.theaterjobs.de
 *
 * @Assert\Expression(
 *   "(this.getFromAge() < this.getToAge()) || !this.requiresAge()",
 *   message="age.from.less.than.age.to"
 * )
 */
class Job extends Inserate implements ProfileJob, Translatable {

    const EMPLOYMENT_STATUS_INACTIVE = 0;
    const EMPLOYMENT_STATUS_AWAITING_ANSWER = 1;
    const EMPLOYMENT_STATUS_SUCCESSFULL = 2; // when the employee is found from theaterjobs
    const EMPLOYMENT_STATUS_FAILED = 3; // when the employee is not found from theaterjobs
    const EMPLOYMENT_STATUS_UNANSWERED = 4;
    const MODE_ORGANIZATION = 1;
    const MODE_INDIVIDUAL = 2;
    const PENDING_LIMIT = 10;

    // Job statuses publication, pending action
    // Awaiting admin approval.
    const WAITING_ADMIN_APPROVE = 1;
    // Awaiting team member approval.
    const WAITING_TEAM_APPROVE = 2;
    // Awaiting email confirmation.
    const WAITING_EMAIL_CONFIRM = 3;
    // awaiting organization approval.
    const WAITING_ORGA_APPROVE = 4;

    /**
     * The Discriminator-Map is defined in the parent class.
     *
     * @var unknown
     * @var unknown
     */
    protected $subdir = 'jobs';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\InserateBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="tj_inserate_users_secondtcheck_id")
     */
    protected $secondCheck;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hideOrganizationLogo", type="boolean")
     */
    protected $hideOrganizationLogo = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archivedAt", type="datetime", nullable=true)
     */
    protected $archivedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="statusChangedAt", type="datetime", nullable=true)
     */
    protected $statusChangedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="watch_list", type="boolean")
     */
    protected $watchList = false;

    /**
     * @return bool
     */
    public function isWatchList()
    {
        return $this->watchList;
    }

    /**
     * @param bool $watchList
     * @return Job
     */
    public function setWatchList($watchList)
    {
        $this->watchList = $watchList;
        return $this;
    }

    /**
     * @ORM\Column( name="contact", type="string", length=1024, nullable=true)
     */
    protected $contact;

    /**
     * @ORM\Column( name="copyright_text", type="string", length=255, nullable=true)
     */
    protected $copyrightText;

    /**
     * @Gedmo\Translatable
     * @ORM\Column( name="fromAge", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 99,
     *      minMessage = "age.greater_equal.than.one",
     *      maxMessage = "age.lower_equal.than.ninety_nine"
     * )
     */
    protected $fromAge;

    /**
     * @Gedmo\Translatable
     * @ORM\Column( name="toAge", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 99,
     *      minMessage = "age.greater_equal.than.one",
     *      maxMessage = "age.lower_equal.than.ninety_nine"
     * )
     */
    protected $toAge;

    /**
     * @ORM\Column(name="do_not_use_application_system",type="boolean")
     * */
    protected $otherApplicationWay = false;

    /**
     * @ORM\OneToMany(targetEntity="\Theaterjobs\InserateBundle\Entity\Jobmail", mappedBy="job")
     */
    private $jobmail;

    /**
     * @ORM\Column( name="is_queued", type="boolean")
     */
    protected $isQueued = false;

    /**
     * @ORM\Column(name="employment_status", type="smallint")
     */
    protected $employmentStatus = self::EMPLOYMENT_STATUS_INACTIVE;

    /**
     * @ORM\Column(name="employment_date", type="datetime", nullable=true)
     */
    protected $employmentDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="job_from_other_site", type="boolean")
     */
    protected $jobFromOtherSite = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_only_for_admins", type="boolean")
     */
    protected $onlyForAdmins = false;

    /**
     * @ORM\OneToOne(targetEntity="ApplicationRejectDraft", mappedBy="job")
     * */
    protected $rejectDraft;

    /**
     * @var boolean
     *
     * @ORM\Column(name="send_job_mail", type="boolean")
     */
    protected $sendJobMail = true;

    /**
     * @var string
     *
     * @ORM\Column(name="pathCover", type="string", length=255, nullable=true)
     */
    protected $pathCover;


    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="logo", fileNameProperty="pathCover")
     *
     * @Assert\Image(
     *     mimeTypes = "image/*",
     *     maxSize = "10M",
     * )
     *
     * @var File
     */
    protected $uploadFileCover;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", mappedBy="jobFavourite")
     */
    protected $profileFavourites;

    /**
     * @ORM\Column( name="create_mode",type="integer", nullable=true)
     */
    protected $createMode;

    /**
     * @return mixed
     */
    public function getProfileFavourites()
    {
        return $this->profileFavourites;
    }

    public function addProfileFavourites(Profile $profileFavourites)
    {
        $this->profileFavourites[] = $profileFavourites;
        return $this;
    }

    public function removeProfileFavourites(Profile $profileFavourites)
    {
        $this->profileFavourites->removeElement($profileFavourites);
    }


    public function __construct()
    {
        $this->profileFavourites = new ArrayCollection();
    }

    /**
     * (non-PHPdoc).
     *
     * @see LogoPossessor::getType()
     *
     * @return string
     */
    public function getType() {
        return 'tj_inserate_jobs';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set hideOrganizationLogo.
     *
     * @param bool $hideOrganizationLogo
     *
     * @return Job
     */
    public function setHideOrganizationLogo($hideOrganizationLogo) {
        $this->hideOrganizationLogo = $hideOrganizationLogo;

        return $this;
    }

    /**
     * Get hideOrganizationLogo.
     *
     * @return bool
     */
    public function getHideOrganizationLogo() {
        return $this->hideOrganizationLogo;
    }

    /**
     * Set contact.
     *
     * @param string $contact
     *
     * @return Job
     */
    public function setContact($contact) {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact.
     *
     * @return string
     */
    public function getContact() {
        return $this->contact;
    }

    /**
     * Set qualifications.
     *
     * @param QualificationInterface|null $qualification
     * @return Job
     * @internal param QualificationInterface $qualifications
     *
     */
    public function setQualification(QualificationInterface $qualification = null) {
        $this->qualification = $qualification;

        return $this;
    }

    /**
     * Set profile.
     *
     * @param ProfileInterface|null $profile
     *
     * @return Job
     */
    public function setProfile(ProfileInterface $profile = null) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile.
     *
     * @return ProfileInterface
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * Set secondCheck.
     *
     *
     * @param UserInterface|null $secondCheck
     * @return Job
     */
    public function setSecondCheck(UserInterface $secondCheck = null) {
        $this->secondCheck = $secondCheck;

        return $this;
    }

    /**
     * Get secondCheck.
     *
     * @return UserInterface
     */
    public function getSecondCheck() {
        return $this->secondCheck;
    }

    public function setFromAge($fromAge) {
        $this->fromAge = $fromAge;
    }

    public function getFromAge() {
        return $this->fromAge;
    }

    public function setToAge($toAge) {
        $this->toAge = $toAge;
    }

    public function getToAge() {
        return $this->toAge;
    }


    /**
     * Set otherApplicationWay.
     *
     * @param bool $otherApplicationWay
     *
     * @return Job
     */
    public function setOtherApplicationWay($otherApplicationWay) {
        $this->otherApplicationWay = $otherApplicationWay;

        return $this;
    }

    /**
     * Get otherApplicationWay.
     *
     * @return boolean
     */
    public function getOtherApplicationWay() {
        return $this->otherApplicationWay;
    }

    /**
     * Add jobmail.
     *
     * @param Jobmail $jobmail
     *
     * @return Job
     */
    public function addJobmail(Jobmail $jobmail) {
        $this->jobmail[] = $jobmail;

        return $this;
    }

    /**
     * Remove jobmail.
     *
     * @param Jobmail $jobmail
     */
    public function removeJobmail(Jobmail $jobmail) {
        $this->jobmail->removeElement($jobmail);
    }

    /**
     * Get jobmail.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobmail() {
        return $this->jobmail;
    }

    public function requiresAge() {
        $result = false;
        foreach ($this->getCategories() as $cat) {
            $result |= $cat->getRequiresAge();
        }

        return $result;
    }

    /**
     * Set isQueued.
     *
     * @param bool $isQueued
     *
     * @return Job
     */
    public function setIsQueued($isQueued) {
        $this->isQueued = $isQueued;

        return $this;
    }

    /**
     * Get isQueued.
     *
     * @return bool
     */
    public function getIsQueued() {
        return $this->isQueued;
    }

    /**
     * Set employmentStatus.
     *
     * @param int $employmentStatus
     *
     * @return Job
     */
    public function setEmploymentStatus($employmentStatus) {
        $this->employmentStatus = $employmentStatus;

        return $this;
    }

    /**
     * Get employmentStatus.
     *
     * @return int
     */
    public function getEmploymentStatus() {
        return $this->employmentStatus;
    }

    /**
     * Set employmentDate.
     *
     * @param \DateTime $employmentDate
     *
     * @return Job
     */
    public function setEmploymentDate($employmentDate) {
        $this->employmentDate = $employmentDate;

        return $this;
    }

    /**
     * Get employmentDate.
     *
     * @return \DateTime
     */
    public function getEmploymentDate() {
        return $this->employmentDate;
    }

    function getJobFromOtherSite() {
        return $this->jobFromOtherSite;
    }

    function setJobFromOtherSite($jobFromOtherSite) {
        $this->jobFromOtherSite = $jobFromOtherSite;
    }

    function getOnlyForAdmins() {
        return $this->onlyForAdmins;
    }

    function setOnlyForAdmins($onlyForAdmins) {
        $this->onlyForAdmins = $onlyForAdmins;
    }

    function getRejectDraft() {
        return $this->rejectDraft;
    }

    function setRejectDraft($rejectDraft) {
        $this->rejectDraft = $rejectDraft;
    }

    function getSendJobMail() {
        return $this->sendJobMail;
    }

    function setSendJobMail($sendJobMail) {
        $this->sendJobMail = $sendJobMail;
    }

    public function getPathCover(){
        return $this->pathCover;
    }

    public function setPathCover($pathCover){
        $this->pathCover = $pathCover;
        return $this;
    }

    public function getUploadFileCover(){
        return $this->uploadFileCover;
    }

    public function setUploadFileCover($uploadFileCover){
        $this->uploadFileCover = $uploadFileCover;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateMode()
    {
        return $this->createMode;
    }

    /**
     * @param mixed $createMode
     */
    public function setCreateMode($createMode)
    {
        $this->createMode = $createMode;
    }


    /**
     * @return \DateTime
     */
    public function getStatusChangedAt()
    {
        return $this->statusChangedAt;
    }

    /**
     * @param \DateTime $statusChangedAt
     * @return Job
     */
    public function setStatusChangedAt($statusChangedAt)
    {
        $this->statusChangedAt = $statusChangedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getCopyrightText()
    {
        return $this->copyrightText;
    }

    /**
     * @param string $copyrightText
     * @return Job
     */
    public function setCopyrightText($copyrightText)
    {
        $this->copyrightText = $copyrightText;
        return $this;
    }



    /**
     * Check if job is deleted
     */
    public function isDeleted()
    {
        return $this->getStatus() == self::STATUS_DELETED;
    }
    /**
     * Check if job is deleted
     */
    public function isDraft()
    {
        return $this->getStatus() == self::STATUS_DRAFT;
    }
    /**
     * Check if job is deleted
     */
    public function isArchived()
    {
        return $this->getStatus() == self::STATUS_ARCHIVED;
    }
    /**
     * Check if job is deleted
     */
    public function isPublished()
    {
        return $this->getStatus() == self::STATUS_PUBLISHED;
    }
    /**
     * Check if job is deleted
     */
    public function isPending()
    {
        return $this->getStatus() == self::STATUS_PENDING;
    }

}
