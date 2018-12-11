<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Model\ProfileInterface as MembershipProfile;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\UserBundle\Model\ProfileInterface as UserProfile;
use Theaterjobs\InserateBundle\Model\ProfileInterface as InseratProfile;
use Theaterjobs\StatsBundle\Model\ViewableInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * Profile
 *
 * @ORM\Table(name="tj_profile_profiles")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\ProfileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Profile implements MembershipProfile, UserProfile, InseratProfile, ViewableInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Model\UserInterface", mappedBy="profile", cascade={"persist", "remove"})
     * */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Model\PaymentmethodInterface", mappedBy="blockedForProfiles", fetch="EAGER")
     */
    protected $blockedPaymentmethods;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Model\BillingAddressInterface", mappedBy="profile", cascade={"persist", "remove"})
     */
    protected $billingAddress;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Model\BookingInterface", mappedBy="profile")
     */
    protected $bookings;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Model\SepaMandateInterface", mappedBy="profile")
     */
    protected $sepaMandates;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\ApplicationTrack", mappedBy="profile")
     */
    protected $applicationRequests;

    /**
     * One User has Many VioDone.
     * @ORM\OneToMany(targetEntity="Theaterjobs\AdminBundle\Entity\VioDone", mappedBy="profile")
     */
    private $vioDone;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=128, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=128, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\OneToOne( targetEntity="BiographySection", inversedBy="profile", cascade={"persist"} )
     * @ORM\JoinColumn( name="biography_section",referencedColumnName="id")
     */
    protected $biographySection;

    /**
     * @ORM\OneToMany(targetEntity="ProductionParticipations", mappedBy="profile")
     * @ORM\OrderBy({"start" = "DESC"})
     */
    protected $productionParticipations;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Experience", mappedBy="profile")
     * @ORM\OrderBy({"start" = "DESC"})
     */
    protected $experience;


    /**
     * @var PersonalData
     * @ORM\OneToOne(targetEntity="PersonalData" , mappedBy="profile", cascade={"persist"})
     */
    private $personalData;

    /**
     * @var SkillSection
     * @ORM\OneToOne( targetEntity="SkillSection", inversedBy="profile" , cascade={"persist"})
     * @ORM\JoinColumn( name="skill_section",referencedColumnName="id",onDelete="SET NULL")
     */
    protected $skillSection;

    /**
     * @ORM\OneToOne( targetEntity="QualificationSection", inversedBy="profile", cascade={"persist"} )
     * @ORM\JoinColumn( name="qualification_section",referencedColumnName="id")
     */
    protected $qualificationSection;

    /**
     * @ORM\OneToOne( targetEntity="ContactSection", inversedBy="profile", cascade={"persist"} )
     * @ORM\JoinColumn( name="contact_section",referencedColumnName="id")
     */
    protected $contactSection;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string", length=128, nullable=true)
     */
    protected $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="undertitle", type="string", length=140, nullable=true)
     */
    protected $subtitle2;

    /**
     * @Gedmo\Slug(
     *     fields={"subtitle"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", length=128)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="availableLocations", type="string" , length=256, nullable=true)
     */
    protected $availableLocations;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPublished", type="boolean", nullable=true)
     */
    protected $isPublished = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="unPublishedAt", type="datetime", nullable=true)
     *
     */
    protected $unPublishedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVisible", type="boolean", nullable=true)
     */
    protected $isVisible;

    /**
     * @var boolean
     * @ORM\Column(name="showWizard",type="boolean", nullable=true)
     */
    protected $showWizard;

    /**
     * @var integer
     * @ORM\Column( name="usedSpace", type="integer", nullable=true))
     */
    protected $usedSpace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     *
     * @var integer
     */
    protected $limit;

    /**
     * @ORM\OneToMany( targetEntity="Skill" , mappedBy="inserter" )
     */
    protected $skillInserter;

    /**
     * @ORM\OneToMany(targetEntity="MediaImage", mappedBy="profile", cascade={"persist","remove"})
     * @OrderBy({"isProfilePhoto" = "DESC", "updatedAt" = "DESC"})
     */
    protected $mediaImage;

    /**
     * @ORM\OneToMany(targetEntity="MediaAudio", mappedBy="profile", cascade={"persist","remove"})
     * @OrderBy({"updatedAt" = "DESC"})
     */
    protected $mediaAudio;

    /**
     * @ORM\OneToMany(targetEntity="MediaPdf", mappedBy="profile", cascade={"persist","remove"})
     * @OrderBy({"updatedAt" = "DESC"})
     */
    protected $mediaPdf;

    /**
     * @ORM\OneToMany(targetEntity="EmbededVideos", mappedBy="profile", cascade={"persist","remove"})
     * @OrderBy({"updatedAt" = "DESC"})
     */
    protected $videos;

    /**
     * @ORM\OneToOne(targetEntity="ProfileAllowedTo", inversedBy="profile", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="profile_allowed_to", referencedColumnName="id")
     * */
    protected $profileAllowedTo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirmedAt", type="datetime", nullable=true)
     *
     */
    protected $confirmedAt;

    /**
     * @var boolean
     * @ORM\Column(name="in_admin_check_list",type="boolean", nullable=true)
     */
    protected $inAdminCheckList = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRevokedBefore", type="boolean", nullable=true)
     */
    protected $isRevokedBefore = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="adminIsWorking", type="boolean", nullable=true)
     */
    protected $adminIsWorking = false;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Entity\OrganizationEnsemble", mappedBy="users", cascade={"persist"})
     * */
    protected $organizationEnsemble;

    /**
     * @var string
     * @ORM\Column(name="profile_actuality_text", type="text", nullable=true)
     *
     */
    protected $profileActualityText;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\MainBundle\Entity\SaveSearch", mappedBy="profile")
     */
    protected $searches;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="profile_actuality_date", type="datetime", nullable=true)
     */
    protected $profileActualityDate;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile")
     * @ORM\JoinTable(name="tj_profile_profiles_favourites",
     *      joinColumns={@ORM\JoinColumn(name="profile_from_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="profile_to_id", referencedColumnName="id")}
     *      )
     */
    protected $userFavourite;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\NewsBundle\Entity\News", inversedBy="profileFavourites")
     * @ORM\JoinTable(name="tj_news_profiles_favourites",
     *          joinColumns={@ORM\JoinColumn(name="profile_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="news_id", referencedColumnName="id")}
     * )
     */
    protected $newsFavourite;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Entity\Job", inversedBy="profileFavourites")
     * @ORM\JoinTable(name="tj_job_profiles_favourites",
     *          joinColumns={@ORM\JoinColumn(name="profile_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="job_id", referencedColumnName="id")}
     * )
     */
    protected $jobFavourite;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Entity\Organization", inversedBy="profileFavorite")
     * @ORM\JoinTable(name="tj_organization_profile_favourites",
     *          joinColumns={@ORM\JoinColumn(name="profile_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="organization_id", referencedColumnName="id")}
     * )
     */
    protected $organisationFavourite;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="revokedAt", type="datetime", nullable=true)
     *
     */
    protected $revokedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_name", type="boolean")
     */
    private $profileName = true;

    /**
     * @var string
     *
     * @ORM\Column(name="do_not_track_views", type="boolean")
     */
    private $doNotTrackViews = true;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\OldExperience", mappedBy="profile", cascade={"persist", "remove"})
     * */
    protected $oldExperience;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\OldEducation", mappedBy="profile", cascade={"persist", "remove"})
     * */
    protected $oldEducation;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\OldExtras", mappedBy="profile", cascade={"persist", "remove"})
     * */
    protected $oldExtras;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\CategoryBundle\Entity\Category", mappedBy="profiles")
     */
    protected $oldCategories;

    /**
     * @var $oldProfile bool
     *
     * @ORM\Column(name="old_profile", type="boolean")
     */
    protected $oldProfile = false;

    /**
     * @var integer
     * @ORM\Column( name="total_views", type="integer", nullable=true))
     */
    protected $totalViews;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $lastUpdate;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\MembershipBundle\Entity\DebitAccount", mappedBy="profile")
     * */
    private $debitAccount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vioDone = new ArrayCollection();
        $this->skillInserter = new ArrayCollection();
        $this->mediaImage = new ArrayCollection();
        $this->mediaAudio = new ArrayCollection();
        $this->mediaPdf = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->blockedPaymentmethods = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->sepaMandates = new ArrayCollection();
        $this->productionParticipations = new ArrayCollection();
        $this->experience = new ArrayCollection();
        $this->oldCategories = new ArrayCollection();
        $this->newsFavourite = new ArrayCollection();
        $this->organisationFavourite = new ArrayCollection();
        $this->searches = new ArrayCollection();
        $this->jobFavourite = new ArrayCollection();
        $this->applicationRequests = new ArrayCollection();
        $this->userFavourite = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDebitAccount()
    {
        return $this->debitAccount;
    }

    /**
     * @param mixed $debitAccount
     */
    public function setDebitAccount($debitAccount)
    {
        $this->debitAccount = $debitAccount;
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
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Profile
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Profile
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get profileName
     *
     * @return boolean
     */
    public function getProfileName()
    {
        return $this->profileName;
    }

    /**
     * Set profileName
     *
     * @param boolean $profileName
     *
     * @return Profile
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     *
     * @return Profile
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Profile
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get availableLocations
     *
     * @return string
     */
    public function getAvailableLocations()
    {
        return $this->availableLocations;
    }

    /**
     * Set availableLocations
     *
     * @param string $availableLocations
     *
     * @return Profile
     */
    public function setAvailableLocations($availableLocations)
    {
        $this->availableLocations = $availableLocations;

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     *
     * @return Profile
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isVisible
     *
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set isVisible
     *
     * @param boolean $isVisible
     *
     * @return Profile
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get showWizard
     *
     * @return boolean
     */
    public function getShowWizard()
    {
        return $this->showWizard;
    }

    /**
     * Set showWizard
     *
     * @param boolean $showWizard
     *
     * @return Profile
     */
    public function setShowWizard($showWizard)
    {
        $this->showWizard = $showWizard;

        return $this;
    }

    /**
     * Get usedSpace
     *
     * @return integer
     */
    public function getUsedSpace()
    {
        return $this->usedSpace;
    }

    /**
     * Set usedSpace
     *
     * @param integer $usedSpace
     *
     * @return Profile
     */
    public function setUsedSpace($usedSpace)
    {
        $this->usedSpace = $usedSpace;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Profile
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Profile
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get confirmedAt
     *
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * Set confirmedAt
     *
     * @param \DateTime $confirmedAt
     *
     * @return Profile
     */
    public function setConfirmedAt($confirmedAt)
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * Get revokedAt
     *
     * @return \DateTime
     */
    public function getRevokedAt()
    {
        return $this->revokedAt;
    }

    /**
     * Set revokedAt
     *
     * @param \DateTime $revokedAt
     *
     * @return Profile
     */
    public function setRevokedAt($revokedAt)
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Add experience
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Experience $experience
     *
     * @return Profile
     */
    public function addExperience(Experience $experience)
    {
        $this->experience[] = $experience;

        return $this;
    }

    /**
     * Remove Experience
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Experience $experience
     */
    public function removeExperience(Experience $experience)
    {
        $this->experience->removeElement($experience);
    }

    /**
     * Get Experience
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExperiences()
    {
        return $this->experience;
    }

    /**
     * Add ProductionParticipations
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProductionParticipations $productionParticipations
     *
     * @return Profile
     */
    public function addProductionParticipations(ProductionParticipations $productionParticipations)
    {
        $this->productionParticipations[] = $productionParticipations;

        return $this;
    }

    /**
     * Remove productionParticipations
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProductionParticipations $productionParticipations
     */
    public function removeProductionParticipations(ProductionParticipations $productionParticipations)
    {
        $this->productionParticipations->removeElement($productionParticipations);
    }

    /**
     * Get productionParticipations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductionParticipations()
    {
        return $this->productionParticipations;
    }

    /**
     * Get biographySection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\BiographySection
     */
    public function getBiographySection()
    {
        return $this->biographySection;
    }

    /**
     * Set biographySection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\BiographySection $biographySection
     *
     * @return Profile
     */
    public function setBiographySection(\Theaterjobs\ProfileBundle\Entity\BiographySection $biographySection = null)
    {
        $this->biographySection = $biographySection;

        return $this;
    }

    /**
     * Get skillSection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\SkillSection
     */
    public function getSkillSection()
    {
        return $this->skillSection;
    }

    /**
     * Set skillSection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\SkillSection $skillSection
     *
     * @return Profile
     */
    public function setSkillSection(\Theaterjobs\ProfileBundle\Entity\SkillSection $skillSection = null)
    {
        $this->skillSection = $skillSection;

        return $this;
    }

    /**
     * Get qualificationSection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\QualificationSection
     */
    public function getQualificationSection()
    {
        return $this->qualificationSection;
    }

    /**
     * Set qualificationSection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\QualificationSection $qualificationSection
     *
     * @return Profile
     */
    public function setQualificationSection(\Theaterjobs\ProfileBundle\Entity\QualificationSection $qualificationSection = null)
    {
        $this->qualificationSection = $qualificationSection;

        return $this;
    }

    /**
     * Get contactSection
     *
     * @return \Theaterjobs\ProfileBundle\Entity\ContactSection
     */
    public function getContactSection()
    {
        return $this->contactSection;
    }

    /**
     * Set contactSection
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ContactSection $contactSection
     *
     * @return Profile
     */
    public function setContactSection(\Theaterjobs\ProfileBundle\Entity\ContactSection $contactSection = null)
    {
        $this->contactSection = $contactSection;

        return $this;
    }

    /**
     * Add skillInserter
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Skill $skillInserter
     *
     * @return Profile
     */
    public function addSkillInserter(\Theaterjobs\ProfileBundle\Entity\Skill $skillInserter)
    {
        $skillInserter->setProfile($this);
        $this->skillInserter[] = $skillInserter;

        return $this;
    }

    /**
     * Remove skillInserter
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Skill $skillInserter
     */
    public function removeSkillInserter(\Theaterjobs\ProfileBundle\Entity\Skill $skillInserter)
    {
        $this->skillInserter->removeElement($skillInserter);
    }

    /**
     * Get skillInserter
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSkillInserter()
    {
        return $this->skillInserter;
    }


    /**
     * Add mediaImage
     *
     * @param MediaImage $mediaImage
     *
     * @return Profile
     */
    public function addMediaImage(MediaImage $mediaImage)
    {
        $mediaImage->setProfile($this);

        if (!$this->mediaImage->contains($mediaImage)) {
            $this->mediaImage->add($mediaImage);
        }


        // Collect an array iterator.
        $iterator = $this->mediaImage->getIterator();

        // Do sort the new iterator.
        // Since the new object is added dynamically not coming directly from doctrine we need to make a manual sort
        $iterator->uasort(function ($a, $b) {
            return ($a->getUpdatedAt() > $b->getUpdatedAt()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->mediaImage = new ArrayCollection(iterator_to_array($iterator));

        return $this;
    }

    /**
     * Remove mediaImage
     *
     * @param \Theaterjobs\ProfileBundle\Entity\MediaImage $mediaImage
     */
    public function removeMediaImage(MediaImage $mediaImage)
    {
        $this->mediaImage->removeElement($mediaImage);
    }

    /**
     * Get mediaImage
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMediaImage()
    {
        return $this->mediaImage;
    }

    /**
     * Add mediaAudio
     *
     * @param MediaAudio $mediaAudio
     *
     * @return Profile
     */
    public function addMediaAudio(MediaAudio $mediaAudio)
    {
        $mediaAudio->setProfile($this);

        if (!$this->mediaAudio->contains($mediaAudio)) {
            $this->mediaAudio->add($mediaAudio);
        }

        // Collect an array iterator.
        $iterator = $this->mediaAudio->getIterator();

        // Do sort the new iterator.
        // Since the new object is added dynamically not coming directly from doctrine we need to make a manual sort
        $iterator->uasort(function ($a, $b) {
            return ($a->getUpdatedAt() > $b->getUpdatedAt()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->mediaAudio = new ArrayCollection(iterator_to_array($iterator));

        return $this;
    }

    /**
     * Remove mediaAudio
     *
     * @param \Theaterjobs\ProfileBundle\Entity\MediaAudio $mediaAudio
     */
    public function removeMediaAudio(MediaAudio $mediaAudio)
    {
        $this->mediaAudio->removeElement($mediaAudio);
    }

    /**
     * Get mediaAudio
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMediaAudio()
    {
        return $this->mediaAudio;
    }

    /**
     * Add mediaPdf
     *
     * @param MediaPdf $mediaPdf
     *
     * @return Profile
     */
    public function addMediaPdf(MediaPdf $mediaPdf)
    {
        $mediaPdf->setProfile($this);

        if (!$this->mediaPdf->contains($mediaPdf)) {
            $this->mediaPdf->add($mediaPdf);
        }

        // Collect an array iterator.
        $iterator = $this->mediaPdf->getIterator();

        // Do sort the new iterator.
        // Since the new object is added dynamically not coming directly from doctrine we need to make a manual sort
        $iterator->uasort(function ($a, $b) {
            return ($a->getUpdatedAt() > $b->getUpdatedAt()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->mediaPdf = new ArrayCollection(iterator_to_array($iterator));

        return $this;
    }

    /**
     * Remove mediaPdf
     *
     * @param MediaPdf $mediaPdf
     */
    public function removeMediaPdf(MediaPdf $mediaPdf)
    {
        $mediaPdf->setProfile(null);
        $this->mediaPdf->removeElement($mediaPdf);
    }

    /**
     * Get mediaPdf
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMediaPdf()
    {
        return $this->mediaPdf;
    }

    /**
     * Add video
     *
     * @param EmbededVideos $embededVideos
     *
     * @return Profile
     */
    public function addVideo(EmbededVideos $embededVideos)
    {
        $embededVideos->setProfile($this);

        if (!$this->videos->contains($embededVideos)) {
            $this->videos->add($embededVideos);
        }

        // Collect an array iterator.
        $iterator = $this->videos->getIterator();

        // Do sort the new iterator.
        // Since the new object is added dynamically not coming directly from doctrine we need to make a manual sort
        $iterator->uasort(function ($a, $b) {
            return ($a->getUpdatedAt() > $b->getUpdatedAt()) ? -1 : 1;
        });


        // pass sorted array to a new ArrayCollection.
        $this->videos = new ArrayCollection(iterator_to_array($iterator));

        return $this;
    }

    /**
     * Remove video
     *
     * @param \Theaterjobs\ProfileBundle\Entity\EmbededVideos $video
     */
    public function removeVideo(EmbededVideos $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Get profileAllowedTo
     *
     * @return \Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo
     */
    public function getProfileAllowedTo()
    {
        return $this->profileAllowedTo;
    }

    /**
     * Set profileAllowedTo
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo $profileAllowedTo
     *
     * @return Profile
     */
    public function setProfileAllowedTo(\Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo $profileAllowedTo = null)
    {
        $this->profileAllowedTo = $profileAllowedTo;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     *
     * @return Profile
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Add blockedPaymentmethod
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Paymentmethod $blockedPaymentmethod
     *
     * @return Profile
     */
    public function addBlockedPaymentmethod(\Theaterjobs\MembershipBundle\Entity\Paymentmethod $blockedPaymentmethod)
    {
        $blockedPaymentmethod->addBlockedForProfile($this);
        $this->blockedPaymentmethods[] = $blockedPaymentmethod;

        return $this;
    }

    /**
     * Remove blockedPaymentmethod
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Paymentmethod $blockedPaymentmethod
     */
    public function removeBlockedPaymentmethod(\Theaterjobs\MembershipBundle\Entity\Paymentmethod $blockedPaymentmethod)
    {
        $this->blockedPaymentmethods->removeElement($blockedPaymentmethod);
    }

    /**
     * Get blockedPaymentmethods
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlockedPaymentmethods()
    {
        return $this->blockedPaymentmethods;
    }

    /**
     * Get billingAddress
     *
     * @return \Theaterjobs\MembershipBundle\Entity\BillingAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set billingAddress
     *
     * @param \Theaterjobs\MembershipBundle\Entity\BillingAddress $billingAddress
     *
     * @return Profile
     */
    public function setBillingAddress(\Theaterjobs\MembershipBundle\Entity\BillingAddress $billingAddress = null)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Add booking
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $booking
     *
     * @return Profile
     */
    public function addBooking(\Theaterjobs\MembershipBundle\Entity\Booking $booking)
    {
        $this->bookings[] = $booking;

        return $this;
    }

    /**
     * Remove booking
     *
     * @param \Theaterjobs\MembershipBundle\Entity\Booking $booking
     */
    public function removeBooking(\Theaterjobs\MembershipBundle\Entity\Booking $booking)
    {
        $this->bookings->removeElement($booking);
    }

    /**
     * Get bookings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * Add sepaMandate
     *
     * @param \Theaterjobs\MembershipBundle\Entity\SepaMandate $sepaMandate
     *
     * @return Profile
     */
    public function addSepaMandate(\Theaterjobs\MembershipBundle\Entity\SepaMandate $sepaMandate)
    {
        $this->sepaMandates[] = $sepaMandate;

        return $this;
    }

    /**
     * Remove sepaMandate
     *
     * @param \Theaterjobs\MembershipBundle\Entity\SepaMandate $sepaMandate
     */
    public function removeSepaMandate(\Theaterjobs\MembershipBundle\Entity\SepaMandate $sepaMandate)
    {
        $this->sepaMandates->removeElement($sepaMandate);
    }

    /**
     * Get sepaMandates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSepaMandates()
    {
        return $this->sepaMandates;
    }

    function getInAdminCheckList()
    {
        return $this->inAdminCheckList;
    }

    function setInAdminCheckList($inAdminCheckList)
    {
        $this->inAdminCheckList = $inAdminCheckList;
    }

    function getIsRevokedBefore()
    {
        return $this->isRevokedBefore;
    }

    function setIsRevokedBefore($isRevokedBefore)
    {
        $this->isRevokedBefore = $isRevokedBefore;
    }

    function getLimit()
    {
        return $this->limit;
    }

    function setLimit($limit)
    {
        $this->limit = $limit;
    }

    function getAdminIsWorking()
    {
        return $this->adminIsWorking;
    }

    function setAdminIsWorking($adminIsWorking)
    {
        $this->adminIsWorking = $adminIsWorking;
    }

    function getOrganizationEnsemble()
    {
        return $this->organizationEnsemble;
    }

    function setOrganizationEnsemble($organizationEnsemble)
    {
        $this->organizationEnsemble = $organizationEnsemble;
    }

    function getProfileActualityText()
    {
        return $this->profileActualityText;
    }

    function setProfileActualityText($profileActualityText)
    {
        $this->profileActualityText = $profileActualityText;
    }

    function getProfileActualityDate()
    {
        return $this->profileActualityDate;
    }

    function setProfileActualityDate(\DateTime $profileActualityDate)
    {
        $this->profileActualityDate = $profileActualityDate;
    }

    function getSubtitle2()
    {
        return $this->subtitle2;
    }

    function setSubtitle2($subtitle2)
    {
        $this->subtitle2 = $subtitle2;
    }

    /**
     * Add organizationEnsemble
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble
     *
     * @return Profile
     */
    public function addOrganizationEnsemble(\Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble)
    {
        $this->organizationEnsemble[] = $organizationEnsemble;

        return $this;
    }

    /**
     * Remove organizationEnsemble
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble
     */
    public function removeOrganizationEnsemble(\Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble)
    {
        $this->organizationEnsemble->removeElement($organizationEnsemble);
    }

    /**
     * Add userFavourite
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $userFavourite
     *
     * @return Profile
     */
    public function addUserFavourite(\Theaterjobs\ProfileBundle\Entity\Profile $userFavourite)
    {
        $this->userFavourite[] = $userFavourite;

        return $this;
    }

    /**
     * Remove userFavourite
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $userFavourite
     */
    public function removeUserFavourite(\Theaterjobs\ProfileBundle\Entity\Profile $userFavourite)
    {
        $this->userFavourite->removeElement($userFavourite);
    }

    /**
     * Get userFavourite
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserFavourite()
    {
        return $this->userFavourite;
    }

    /**
     * get Organization favourite ids
     * @return array
     */
    public function getUserFavouritesIds()
    {
        return array_reduce($this->userFavourite->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }

    function getDoNotTrackViews()
    {
        return $this->doNotTrackViews;
    }

    function setDoNotTrackViews($doNotTrackViews)
    {
        $this->doNotTrackViews = $doNotTrackViews;
    }

    /**
     * Set oldExperience
     *
     * @param \Theaterjobs\ProfileBundle\Entity\OldExperience $oldExperience
     *
     * @return Profile
     */
    public function setOldExperience(\Theaterjobs\ProfileBundle\Entity\OldExperience $oldExperience = null)
    {
        $this->oldExperience = $oldExperience;

        return $this;
    }

    /**
     * Get oldExperience
     *
     * @return \Theaterjobs\ProfileBundle\Entity\OldExperience
     */
    public function getOldExperience()
    {
        return $this->oldExperience;
    }

    /**
     * Set oldEducation
     *
     * @param \Theaterjobs\ProfileBundle\Entity\OldEducation $oldEducation
     *
     * @return Profile
     */
    public function setOldEducation(\Theaterjobs\ProfileBundle\Entity\OldEducation $oldEducation = null)
    {
        $this->oldEducation = $oldEducation;

        return $this;
    }

    /**
     * Get oldEducation
     *
     * @return \Theaterjobs\ProfileBundle\Entity\OldEducation
     */
    public function getOldEducation()
    {
        return $this->oldEducation;
    }

    /**
     * Set oldExtras
     *
     * @param \Theaterjobs\ProfileBundle\Entity\OldExtras $oldExtras
     *
     * @return Profile
     */
    public function setOldExtras(\Theaterjobs\ProfileBundle\Entity\OldExtras $oldExtras = null)
    {
        $this->oldExtras = $oldExtras;

        return $this;
    }

    /**
     * Get oldExtras
     *
     * @return \Theaterjobs\ProfileBundle\Entity\OldExtras
     */
    public function getOldExtras()
    {
        return $this->oldExtras;
    }

    /**
     * Add oldCategory
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $oldCategory
     *
     * @return Profile
     */
    public function addOldCategory(\Theaterjobs\CategoryBundle\Entity\Category $oldCategory)
    {
        $this->oldCategories[] = $oldCategory;

        return $this;
    }

    /**
     * Remove oldCategory
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $oldCategory
     */
    public function removeOldCategory(\Theaterjobs\CategoryBundle\Entity\Category $oldCategory)
    {
        if (!$this->oldCategories->contains($oldCategory)) {
            return;
        }
        $this->oldCategories->removeElement($oldCategory);
        $oldCategory->removeProfile($this);
    }

    /**
     * Get oldCategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOldCategories()
    {
        return $this->oldCategories;
    }

    /**
     * @return mixed
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * @param mixed $experience
     * @return Profile
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;
        return $this;
    }

    /**
     * Get full name of Profile
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    /**
     * @return mixed
     */
    public function getOrganisationFavourite()
    {
        return $this->organisationFavourite;
    }

    /**
     * get Organization favourite ids
     * @return array
     */
    public function getOrganizationFavouriteIds()
    {
        return array_reduce($this->organisationFavourite->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }

    /**
     * @return mixed
     */
    public function getSearches()
    {
        return $this->searches;
    }

    public function addSearches($search)
    {
        $this->searches[] = $search;
        return $this;
    }

    public function removeSearches($search)
    {
        $this->searches->removeElement($search);
    }

    /**
     * @return mixed
     */
    public function getJobFavourite()
    {
        return $this->jobFavourite;
    }

    /**
     * Get job favourite ids
     */
    public function getJobFavouriteIds()
    {
        return array_reduce($this->jobFavourite->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }


    public function getJobFavouriteFiltered()
    {
        $idsToFilter = [1, 2, 3, 5];
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('status', $idsToFilter));
    }


    /**
     * @param mixed $jobFavourite
     * @return Profile
     */
    public function addJobFavourite($jobFavourite)
    {
        $this->jobFavourite[] = $jobFavourite;
        return $this;
    }

    /**
     * @param $jobFavourite
     */
    public function removeJobFavourite($jobFavourite)
    {
        $this->jobFavourite->removeElement($jobFavourite);
    }

    /**
     * @param $organisationFavourite
     * @return $this
     */
    public function addOrganisationFavourite($organisationFavourite)
    {
        $this->organisationFavourite[] = $organisationFavourite;
        return $this;
    }

    /**
     * @param $organisationFavourite
     */
    public function removeOrganisationFavourite($organisationFavourite)
    {
        $this->organisationFavourite->removeElement($organisationFavourite);
    }

    /**
     * @return mixed
     */
    public function getNewsFavourite()
    {
        return $this->newsFavourite;
    }

    /**
     * get Organization favourite ids
     * @return array
     */
    public function getNewsFavouriteIds()
    {
        return array_reduce($this->newsFavourite->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }

    /**
     * @param News $newsFavourite
     * @return $this
     */
    public function addNewsFavourite(News $newsFavourite)
    {
        $this->newsFavourite[] = $newsFavourite;
        return $this;
    }

    /**
     * @param mixed $newsFavourite
     */
    public function removeNewsFavourite(News $newsFavourite)
    {
        $this->newsFavourite->removeElement($newsFavourite);
    }

    /**
     * @return mixed
     */
    public function getPersonalData()
    {
        return $this->personalData;
    }

    /**
     * @param mixed $personalData
     * @return Profile
     */
    public function setPersonalData($personalData)
    {
        $this->personalData = $personalData;
        $personalData->setProfile($this);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationRequests()
    {
        return $this->applicationRequests;
    }

    /**
     * Get job favourite ids
     */
    public function getJobApplicationIds()
    {
        return array_reduce($this->applicationRequests->toArray(), function ($acc, $item) {
            $acc[] = $item->getId();
            return $acc;
        }, []);
    }

    /**
     * @param mixed $applicationRequests
     * @return Profile
     */
    public function setApplicationRequests($applicationRequests)
    {
        $this->applicationRequests = $applicationRequests;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVioDone()
    {
        return $this->vioDone;
    }

    /**
     * @param mixed $vioDone
     * @return Profile
     */
    public function setVioDone($vioDone)
    {
        $this->vioDone = $vioDone;
        return $this;
    }

    /**
     * @param $oldProfile bool
     */
    public function setOldProfile($oldProfile)
    {
        $this->oldProfile = $oldProfile;
    }

    /**
     * @return bool
     */
    public function getOldProfile()
    {
        return $this->oldProfile;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return \DateTime
     */
    public function getUnPublishedAt()
    {
        return $this->unPublishedAt;
    }

    /**
     * @param \DateTime $unPublishedAt
     * @return Profile
     */
    public function setUnPublishedAt($unPublishedAt)
    {
        $this->unPublishedAt = $unPublishedAt;
        return $this;
    }

    /**
     * Get totalViews
     *
     * @return integer
     */
    public function getTotalViews()
    {
        return $this->totalViews;
    }

    /**
     * Set totalViews
     *
     * @param integer totalViews
     *
     * @return Profile
     */
    public function setTotalViews($totalViews)
    {
        $this->totalViews = $totalViews;

        return $this;
    }

    /**
     * Get last Booking of Profile
     * @return \Theaterjobs\MembershipBundle\Entity\Booking
     */
    public function getLastBooking()
    {
        // Collect an array iterator.
        $iterator = $this->bookings->getIterator();

        // Do sort the new iterator.
        $iterator->uasort(function ($a, $b) {
            return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->bookings = new ArrayCollection(iterator_to_array($iterator));

        return $this->bookings->first();
    }

    public function getProfilePhoto()
    {
        $profilePhoto = $this->mediaImage->filter(
            function ($media) {
                return $media->getIsProfilePhoto() === true;
            }
        );

        return $profilePhoto->current();
    }

    /**
     * Get last SepaMandate of Profile
     * @return \Theaterjobs\MembershipBundle\Entity\SepaMandate
     */
    public function getLastSepaMandate()
    {
        // Collect an array iterator.
        $iterator = $this->sepaMandates->getIterator();

        // Do sort the new iterator.
        $iterator->uasort(function ($a, $b) {
            return ($a->getId() > $b->getId()) ? -1 : 1;
        });

        // pass sorted array to a new ArrayCollection.
        $this->sepaMandates = new ArrayCollection(iterator_to_array($iterator));

        return $this->sepaMandates->first();
    }

    /**
     * Shortcut to booking method
     * @return Billing | null
     */
    public function getLastBilling()
    {
        $lastBooking = $this->getLastBooking();
        return $lastBooking ? $lastBooking->getLastBilling() : null;
    }

    /**
     * Get default name of the profile
     */
    public function defaultName()
    {
        if ($this->getProfileName() && $this->getSubtitle() != '') {
            return $this->getSubtitle();
        } else {
            return $this->getFirstName() . " " . $this->getLastName();
        }
    }

    /**
     * Show profile box content
     *
     * @return bool
     */
    public function showProfileBoxContent()
    {
        return $this->showPersonalData() || $this->showSkillsSection() || !empty($this->availableLocations) || $this->mediaPdf->count() > 0;
    }


    /**
     * Show personal data content
     *
     * @return bool
     */
    public function showPersonalData()
    {
        return !$this->personalData ?: $this->personalData->showContent();

    }


    /**
     * Show skill section content
     *
     * @return bool
     */
    public function showSkillsSection()
    {
        return !$this->skillSection ?: $this->skillSection->showContent();
    }
}
