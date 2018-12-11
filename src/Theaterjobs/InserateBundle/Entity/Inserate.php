<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Theaterjobs\InserateBundle\Model\CategoryInterface;
use Theaterjobs\InserateBundle\Model\UserInterface;
use Theaterjobs\InserateBundle\Model\AddressInterface;
use Theaterjobs\StatsBundle\Model\ViewableInterface;
use Theaterjobs\ProfileBundle\Model\InserateInterface as ProfileInserate;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Entity for the inserate.
 *
 * @ORM\Table(name="tj_inserate_inserates")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\InserateRepository"
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *  "tj_inserate_jobs" = "Job",
 *  "tj_inserate_educations" = "Education",
 *   "tj_inserate_networks" = "Network"
 *
 * })
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
abstract class Inserate extends LogoPossessor implements ProfileInserate, ViewableInterface
{

    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_ARCHIVED = 3;
    const STATUS_DELETED = 4;
    const STATUS_PENDING = 5;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface")
     * @ORM\JoinTable(name="tj_inserate_inserates_favourites",
     *      joinColumns={@ORM\JoinColumn(name="inserate_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    protected $userFavourite;

    /**
     * @ORM\ManyToOne(targetEntity="Inserate", inversedBy="children")
     * @ORM\JoinColumn(name="tj_inserate_parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Inserate", mappedBy="parent")
     */
    protected $children;

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'inserates';

    /**
     * @return string
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param $subdir
     * @return Inserate
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface", fetch="EAGER", inversedBy="inserates")
     * @ORM\JoinColumn(name="tj_inserate_users_id")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="engagement_start", type="date", nullable=true)
     */
    protected $engagementStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="engagement_end", type="date", nullable=true)
     */
    protected $engagementEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="application_end", type="date", nullable=true)
     */
    protected $applicationEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publication_end", type="date", nullable=true)
     */
    protected $publicationEnd;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface",fetch="EAGER")
     * @ORM\JoinTable(name="tj_inserate_inserates_categories",
     *      joinColumns={@ORM\JoinColumn(name="inserate_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    protected $categories;

    /**
     * @ORM\ManyToOne(targetEntity="Gratification", inversedBy="inserates",fetch="EAGER")
     * @ORM\JoinColumn(name="tj_inserate_gratifications_id", referencedColumnName="id")
     */
    protected $gratification;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="inserates", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_inserate_organizations_id", referencedColumnName="id", nullable=true)
     */
    protected $organization;

    /**
     * @ORM\OneToOne(
     *  targetEntity="\Theaterjobs\InserateBundle\Model\AddressInterface", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="tj_inserate_addresses_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $placeOfAction;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @Gedmo\Slug(
     *     fields={"title"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", length=128)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archivedAt", type="datetime", nullable=true)
     */
    protected $archivedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="destroyedAt", type="datetime", nullable=true)
     */
    protected $destroyedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publishedAt", type="datetime", nullable=true)
     */
    protected $publishedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="requestedPublicationAt", type="datetime", nullable=true)
     */
    protected $requestedPublicationAt;


    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="tj_inserate_users_firstcheck_id")
     */
    protected $firstCheck;

    /**
     * @var int
     * @ORM\Column(name="lockFirstTimestamp", type="integer", nullable=true)
     */
    protected $lockFirstTimestamp;

    /**
     * @var int
     * @ORM\Column(name="lockTimestamp", type="integer", nullable=true)
     */
    protected $lockTimestamp;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="tj_inserate_lockusers_id")
     */
    protected $lockUser;

    /**
     * @var boolean
     * @ORM\Column(name="new_published_job", type="boolean")
     */
    protected $newlyPublishedJob = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="asap",type="boolean",nullable=true)
     *
     */
    protected $asap = false;

    /**
     * @ORM\Column(name="archived_views", type="integer",nullable=true)
     */
    protected $archivedViews = 0;

    /**
     * @ORM\Column(name="total_views", type="integer",nullable=true)
     */
    protected $totalViews = 0;


    /**
     * @ORM\OneToMany(targetEntity="MediaImage", mappedBy="inserate", cascade={"persist","remove"})
     */
    private $mediaImage;

    /**
     * @ORM\OneToMany(targetEntity="MediaAudio", mappedBy="inserate", cascade={"persist","remove"})
     */
    private $mediaAudio;

    /**
     * @ORM\OneToMany(targetEntity="MediaPdf", mappedBy="inserate", cascade={"persist","remove"})
     */
    private $mediaPdf;

    /**
     * @ORM\OneToMany(targetEntity="EmbededVideos", mappedBy="inserate", cascade={"persist","remove"})
     */
    private $videos;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\ApplicationTrack", mappedBy="job")
     */
    protected $applicationRequests;

    /**
     * @ORM\Column( name="status",type="integer", nullable=true, options={"default" : 2})
     */
    protected $status = Inserate::STATUS_DRAFT;

    /**
     * 1 - awaiting admin approval.
     * 2 - awaiting team member approval.
     * 3 - awaiting email confirmation.
     * 4 - awaiting organization approval.
     * @ORM\Column( name="pending_action",type="integer", nullable=true, options={"default" : null})
     */
    protected $pendingAction;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AdminComments",
     *     mappedBy="inserate",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    protected $adminComments;

    /**
     *
     * @var boolean
     */
    protected $userHasOrg = false;

    /**
     * @ORM\Column(name="geolocation",type="string", length=255 ,nullable=true)
     */
    protected $geolocation;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_info_box", type="text", nullable=true)
     */
    protected $adminInfoBox;


    /**
     * @ORM\Column(name="contact",type="string", length=1024 ,nullable=true)
     * @Assert\NotBlank()
     */
    protected $contact;

    /**
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email",type="string", length=255 ,nullable=true)
     */
    protected $email;


    /**
     * @var integer
     *
     * @ORM\Column(name="update_counter", type="integer")
     */
    protected $updateCounter = 0;


    /**
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", length=255, nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var boolean
     * @ORM\Column(name="seen", type="boolean", options={"default" : false})
     */
    protected $seen = 0;

    /**
     * @param $bool boolean
     */
    public function setSeen($bool)
    {
        $this->seen = $bool;
    }

    /**
     * @return bool boolean
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * (non-PHPdoc)
     * @see LogoPossessor::getType()
     *
     * @return type of the LogoPossessor
     */
    public function getType()
    {
        return 'tj_inserate_inserates';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->mediaImage = new ArrayCollection();
        $this->mediaAudio = new ArrayCollection();
        $this->mediaPdf = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->children = new ArrayCollection();
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
     * Set parent
     *
     * @param Inserate $parent
     * @return Inserate
     */
    public function setParent(Inserate $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Inserate
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Inserate $children
     * @return Inserate
     */
    public function addChild(Inserate $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Inserate $children
     */
    public function removeChild(Inserate $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set engagementStart
     *
     * @param \DateTime $engagementStart
     * @return Inserate
     */
    public function setEngagementStart($engagementStart)
    {
        $this->engagementStart = $engagementStart;

        return $this;
    }

    /**
     * Get engagementStart
     *
     * @return \DateTime
     */
    public function getEngagementStart()
    {
        return $this->engagementStart;
    }

    /**
     * Set engagementEnd
     *
     * @param \DateTime $engagementEnd
     * @return Inserate
     */
    public function setEngagementEnd($engagementEnd)
    {
        $this->engagementEnd = $engagementEnd;

        return $this;
    }

    /**
     * Get engagementEnd
     *
     * @return \DateTime
     */
    public function getEngagementEnd()
    {
        return $this->engagementEnd;
    }

    /**
     * Set applicationEnd
     *
     * @param \DateTime $applicationEnd
     * @return Inserate
     */
    public function setApplicationEnd($applicationEnd)
    {
        $this->applicationEnd = $applicationEnd;

        return $this;
    }

    /**
     * Get applicationEnd
     *
     * @return \DateTime
     */
    public function getApplicationEnd()
    {
        return $this->applicationEnd;
    }

    /**
     * Set publicationEnd
     *
     * @param \DateTime $publicationEnd
     * @return Inserate
     */
    public function setPublicationEnd($publicationEnd)
    {
        $this->publicationEnd = $publicationEnd;

        return $this;
    }

    /**
     * Get publicationEnd
     *
     * @return \DateTime
     */
    public function getPublicationEnd()
    {
        return $this->publicationEnd;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Inserate
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Inserate
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
     * Set description
     *
     * @param string $description
     * @return Inserate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     * @return Inserate
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * Set destroyedAt
     *
     * @param \DateTime $destroyedAt
     * @return Inserate
     */
    public function setDestroyedAt($destroyedAt)
    {
        $this->destroyedAt = $destroyedAt;

        return $this;
    }

    /**
     * Get destroyedAt
     *
     * @return \DateTime
     */
    public function getDestroyedAt()
    {
        return $this->destroyedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Inserate
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Inserate
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     * @return Inserate
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set lockFirstTimestamp
     *
     * @param integer $lockFirstTimestamp
     * @return Inserate
     */
    public function setLockFirstTimestamp($lockFirstTimestamp)
    {
        $this->lockFirstTimestamp = $lockFirstTimestamp;

        return $this;
    }

    /**
     * Get lockFirstTimestamp
     *
     * @return integer
     */
    public function getLockFirstTimestamp()
    {
        return $this->lockFirstTimestamp;
    }

    /**
     * Set lockTimestamp
     *
     * @param integer $lockTimestamp
     * @return Inserate
     */
    public function setLockTimestamp($lockTimestamp)
    {
        $this->lockTimestamp = $lockTimestamp;

        return $this;
    }

    /**
     * Get lockTimestamp
     *
     * @return integer
     */
    public function getLockTimestamp()
    {
        return $this->lockTimestamp;
    }

    /**
     * @return bool
     */
    public function isNewlyPublishedJob()
    {
        return $this->newlyPublishedJob;
    }

    /**
     * @param bool $newlyPublishedJob
     * @return Inserate
     */
    public function setNewlyPublishedJob($newlyPublishedJob)
    {
        $this->newlyPublishedJob = $newlyPublishedJob;
        return $this;
    }

    /**
     * Set asap
     *
     * @param boolean $asap
     * @return Inserate
     */
    public function setAsap($asap)
    {
        $this->asap = $asap;

        return $this;
    }

    /**
     * Get asap
     *
     * @return boolean
     */
    public function getAsap()
    {
        return $this->asap;
    }

    /**
     * Set gratification
     *
     * @param \Theaterjobs\InserateBundle\Entity\Gratification $gratification
     * @return Inserate
     */
    public function setGratification(\Theaterjobs\InserateBundle\Entity\Gratification $gratification = null)
    {
        $this->gratification = $gratification;

        return $this;
    }

    /**
     * Get gratification
     *
     * @return \Theaterjobs\InserateBundle\Entity\Gratification
     */
    public function getGratification()
    {
        return $this->gratification;
    }

    /**
     * Set organization
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organization
     * @return Inserate
     */
    public function setOrganization(\Theaterjobs\InserateBundle\Entity\Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return Inserate
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add categories
     *
     * @param CategoryInterface $categories
     * @return Inserate
     */
    public function addCategory(CategoryInterface $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param CategoryInterface $categories
     */
    public function removeCategory(CategoryInterface $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set placeOfAction
     *
     * @param AddressInterface
     * @return Inserate
     */
    public function setPlaceOfAction(AddressInterface $placeOfAction = null)
    {
        $this->placeOfAction = $placeOfAction;

        return $this;
    }

    /**
     * Get placeOfAction
     *
     * @return AddressInterface
     */
    public function getPlaceOfAction()
    {
        return $this->placeOfAction;
    }

    /**
     * Set firstCheck
     *
     * @param UserInterface $firstCheck
     * @return Inserate
     */
    public function setFirstCheck(UserInterface $firstCheck = null)
    {
        $this->firstCheck = $firstCheck;

        return $this;
    }

    /**
     * Get firstCheck
     *
     * @return UserInterface
     */
    public function getFirstCheck()
    {
        return $this->firstCheck;
    }

    /**
     * Set lockUser
     *
     * @param UserInterface $lockUser
     * @return Inserate
     */
    public function setLockUser(UserInterface $lockUser = null)
    {
        $this->lockUser = $lockUser;

        return $this;
    }

    /**
     * Get lockUser
     *
     * @return UserInterface
     */
    public function getLockUser()
    {
        return $this->lockUser;
    }

    /**
     * Set archivedViews
     *
     * @param integer $archivedViews
     * @return Inserate
     */
    public function setArchivedViews($archivedViews)
    {
        $this->archivedViews = $archivedViews;

        return $this;
    }

    /**
     * Get archivedViews
     *
     * @return integer
     */
    public function getArchivedViews()
    {
        return $this->archivedViews;
    }

    /**
     * Set totalViews
     *
     * @param integer $totalViews
     * @return Inserate
     */
    public function setTotalViews($totalViews)
    {
        $this->totalViews = $totalViews;

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
     * Add mediaImage
     *
     * @param \Theaterjobs\InserateBundle\Entity\MediaImage $mediaImage
     * @return Profile
     */
    public function addMediaImage(\Theaterjobs\InserateBundle\Entity\MediaImage $mediaImage = null)
    {
        if ($mediaImage !== null) {
            $mediaImage->setInserate($this);
            $this->mediaImage[] = $mediaImage;
        }
        return $this;
    }

    /**
     * Remove mediaImage
     *
     * @param \Theaterjobs\InserateBundle\Entity\MediaImage $mediaImage
     */
    public function removeMediaImage(\Theaterjobs\InserateBundle\Entity\MediaImage $mediaImage)
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
     * @param \Theaterjobs\InserateBundle\Entity\MediaAudio $mediaAudio
     * @return Profile
     */
    public function addMediaAudio(\Theaterjobs\InserateBundle\Entity\MediaAudio $mediaAudio = null)
    {
        if ($mediaAudio !== null) {
            $mediaAudio->setInserate($this);
            $this->mediaAudio[] = $mediaAudio;
        }
        return $this;
    }

    /**
     * Remove mediaAudio
     *
     * @param \Theaterjobs\InserateBundle\Entity\MediaAudio $mediaAudio
     */
    public function removeMediaAudio(\Theaterjobs\InserateBundle\Entity\MediaAudio $mediaAudio)
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
     * @param \Theaterjobs\InserateBundle\Entity\MediaPdf $mediaPdf
     * @return Profile
     */
    public function addMediaPdf(\Theaterjobs\InserateBundle\Entity\MediaPdf $mediaPdf = null)
    {
        if ($mediaPdf !== null) {
            $mediaPdf->setInserate($this);
            $this->mediaPdf[] = $mediaPdf;
        }
        return $this;
    }

    /**
     * Remove mediaPdf
     *
     * @param \Theaterjobs\InserateBundle\Entity\MediaPdf $mediaPdf
     */
    public function removeMediaPdf(\Theaterjobs\InserateBundle\Entity\MediaPdf $mediaPdf)
    {
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
     * Add videos
     *
     * @param \Theaterjobs\InserateBundle\Entity\EmbededVideos $videos
     * @return Profile
     */
    public function addVideo(\Theaterjobs\InserateBundle\Entity\EmbededVideos $videos = null)
    {
        if ($videos !== null) {
            $videos->setInserate($this);
            $this->videos[] = $videos;
        }
        return $this;
    }

    /**
     * Remove videos
     *
     * @param \Theaterjobs\InserateBundle\Entity\EmbededVideos $videos
     */
    public function removeVideo(\Theaterjobs\InserateBundle\Entity\EmbededVideos $videos)
    {
        $this->videos->removeElement($videos);
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
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

//    /**
//     * @ORM\PrePersist()
//     * @ORM\PreUpdate()
//     */
//    public function onPrePersist()
//    {
//        if ($this->getIsDraft()) {
//            $this->setStatus(self::STATUS_DRAFT);
//        } elseif (($this->getOrganization() !== null || $this->getUserHasOrg()) && !$this->getPublishedAt()) {
//            $this->setPublishedAt(new \DateTime());
//            $this->setStatus(self::STATUS_PUBLISHED);
//        }
//    }

//    /**
//     * @ORM\PreUpdate()
//     */
//    public function onPreUpdate(){
//        $counter = $this->getUpdateCounter();
//        $this->setUpdateCounter($counter + 1);
//    }

    /**
     * Add adminComments
     *
     * @param \Theaterjobs\InserateBundle\Entity\AdminComments $adminComments
     * @return Organization
     */
    public function addAdminComment(\Theaterjobs\InserateBundle\Entity\AdminComments $adminComments)
    {
        $adminComments->setInserate($this);
        $this->adminComments[] = $adminComments;

        return $this;
    }

    /**
     * Remove adminComments
     *
     * @param \Theaterjobs\InserateBundle\Entity\AdminComments $adminComments
     */
    public function removeAdminComment(\Theaterjobs\InserateBundle\Entity\AdminComments $adminComments)
    {
        $this->adminComments->removeElement($adminComments);
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
     * Add userFavourite
     *
     * @param \Theaterjobs\UserBundle\Entity\User $userFavourite
     *
     * @return Inserate
     */
    public function addUserFavourite(\Theaterjobs\UserBundle\Entity\User $userFavourite)
    {
        $this->userFavourite[] = $userFavourite;

        return $this;
    }

    /**
     * Remove userFavourite
     *
     * @param \Theaterjobs\UserBundle\Entity\User $userFavourite
     */
    public function removeUserFavourite(\Theaterjobs\UserBundle\Entity\User $userFavourite)
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
     * Set userHasOrg
     *
     * @param boolean $userHasOrg
     *
     * @return Job
     */
    public function setUserHasOrg($userHasOrg)
    {
        $this->userHasOrg = $userHasOrg;

        return $this;
    }

    /**
     * Get userHasOrg
     *
     * @return boolean
     */
    public function getUserHasOrg()
    {
        return $this->userHasOrg;
    }

    function getAdminInfoBox()
    {
        return $this->adminInfoBox;
    }

    function setAdminInfoBox($adminInfoBox)
    {
        $this->adminInfoBox = $adminInfoBox;
    }

    /**
     * Set geolocation
     *
     * @param string $geolocation
     *
     * @return Inserate
     */
    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * Get geolocation
     *
     * @return string
     */
    public function getGeolocation()
    {
        return $this->geolocation;
    }


    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     * @return Inserate
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getUpdateCounter()
    {
        return $this->updateCounter;
    }

    /**
     * @param int $updateCounter
     * @return Inserate
     */
    public function setUpdateCounter($updateCounter)
    {
        $this->updateCounter = $updateCounter;
        return $this;
    }


    /**
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param string $confirmationToken
     * @return Inserate
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getPendingAction()
    {
        return $this->pendingAction;
    }

    /**
     * @param mixed $pendingAction
     * @return Inserate
     */
    public function setPendingAction($pendingAction)
    {
        $this->pendingAction = $pendingAction;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestedPublicationAt()
    {
        return $this->requestedPublicationAt;
    }

    /**
     * @param mixed $requestedPublicationAt
     * @return Inserate
     */
    public function setRequestedPublicationAt($requestedPublicationAt)
    {
        $this->requestedPublicationAt = $requestedPublicationAt;
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
     * @param mixed $applicationRequests
     * @return Inserate
     */
    public function setApplicationRequests($applicationRequests)
    {
        $this->applicationRequests = $applicationRequests;
        return $this;
    }
}
