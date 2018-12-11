<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Theaterjobs\InserateBundle\Model\UserInterface;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Entity\Qualification;
use Theaterjobs\UserBundle\Entity\User;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Theaterjobs\InserateBundle\Model\AddressInterface;
use Theaterjobs\ProfileBundle\Model\OrganizationInterface as ProfileOrganization;
use Theaterjobs\UserBundle\Model\OrganizationInterface as UserOrganization;
use Theaterjobs\NewsBundle\Model\OrganizationInterface as NewsOrganization;
use Theaterjobs\InserateBundle\Model\UserOrganizationInterface;
use Theaterjobs\StatsBundle\Model\ViewableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Theaterjobs\ProfileBundle\Entity\Experience;

/**
 * Entity for the organization.
 *
 * @ORM\Table(name="tj_inserate_organizations")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\OrganizationRepository"
 * )
 * @Vich\Uploadable
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class Organization implements ViewableInterface, ProfileOrganization, UserOrganization, NewsOrganization
{
    const PENDING = 1;
    const ACTIVE = 2;
    const UNKNOWN = 3;
    const CLOSED = 4;

    // @todo we must fix this (using fixed is even that the values are predefined is ricky)
    const SECTION_ORCHESTRA = 6;

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'logo';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One Category has One Vio.
     * @OneToOne(targetEntity="Theaterjobs\AdminBundle\Entity\Vio", mappedBy="organization")
     */
    private $vio;

    /**
     * One Category has One VioDone.
     * @ORM\OneToMany(targetEntity="Theaterjobs\AdminBundle\Entity\VioDone", mappedBy="organization")
     */
    private $vioDone;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="organization", fileNameProperty="path")
     *
     * @Assert\Image(
     *     mimeTypes = "image/*",
     *     maxSize = "10M",
     * )
     *
     * @var File
     */
    protected $uploadFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $path;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Inserate",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     */
    protected $inserates;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Theaterjobs\InserateBundle\Model\UserOrganizationInterface",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     */
    protected $userOrganizations;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", unique=true, length=255)
     * @Assert\Regex("/\w/")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text" , length=4096, nullable=true)
     */
    protected $description;

    /**
     * @Gedmo\Slug(
     *     fields={"name"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", length=255)
     */
    protected $slug;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\InserateBundle\Model\AddressInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="tj_inserate_addresses_id", referencedColumnName="id")
     * */
    protected $address;

    /**
     * @ORM\OneToOne(targetEntity="Theaterjobs\InserateBundle\Entity\ContactSection", inversedBy="organization", cascade={"persist"})
     * @ORM\JoinColumn(name="tj_inserate_contact_section_id", referencedColumnName="id")
     * */
    protected $contactSection;

    /**
     * @ORM\ManyToOne(targetEntity="FormOfOrganization", inversedBy="organizations")
     * @ORM\JoinColumn(
     *     name="tj_inserate_form_of_organizations_id", referencedColumnName="id"
     * )
     */
    protected $form;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_visible_in_list", type="boolean")
     */
    protected $isVisibleInList = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_visible_in_register", type="boolean")
     */
    protected $isVisibleInRegister = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="destroyedAt", type="datetime", nullable=true)
     */
    protected $destroyedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="statusChangedAt", type="datetime", nullable=true)
     */
    protected $statusChangedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    protected $archivedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="notReachableAt", type="datetime", nullable=true)
     */
    protected $notReachableAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AdminComments",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    protected $adminComments;

    /**
     * @ORM\ManyToMany(targetEntity="OrganizationSection", inversedBy="organizations")
     * @ORM\JoinTable(name="tj_inserate_organizations_organization_sections")
     * */
    private $organizationSection;

    /**
     * @ORM\ManyToMany(targetEntity="OrganizationKind", inversedBy="organizations")
     * @ORM\JoinTable(name="tj_inserate_organizations_organization_kind")
     * */
    private $organizationKind;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationGrants", mappedBy="organizations",cascade={"persist"})
     */
    private $organizationGrants;

    /**
     * @var integer
     *
     * @ORM\Column(name="wage_from", type="integer", nullable=true)
     */
    private $wageFrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="wage_to", type="integer", nullable=true)
     */
    private $wageTo;

    /**
     * @var string
     *
     * @ORM\Column(name="organization_owner", type="text", nullable=true)
     */
    private $organizationOwner;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationPerformance", mappedBy="organizations",cascade={"persist"})
     */
    private $organizationPerformance;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationStage", mappedBy="organizations",cascade={"persist"})
     */
    private $organizationStage;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationEnsemble", mappedBy="organization",cascade={"persist"})
     * */
    private $organizationEnsemble;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationStaff", mappedBy="organization",cascade={"persist"})
     * */
    private $organizationStaff;

    /**
     * @ORM\Column( name="OrchestraClass", type="string", nullable=true)
     */
    private $orchestraClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="staff", type="integer", nullable=true)
     */
    private $staff;

    /**
     * @ORM\Column(name="geolocation",type="string", length=255 ,nullable=true)
     */
    public $geolocation;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="application_info_text", type="text" ,  nullable=true)
     *
     */
    protected $organisationApplicationInfoText;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="application_info_date", type="datetime", nullable=true)
     */
    protected $organisationApplicationInfoDate;


    /**
     * (non-PHPdoc)
     * @see LogoPossessor::getType()
     *
     * @return type of the LogoPossessor
     */
    public function getType()
    {
        return 'tj_inserate_organizations';
    }

    /**
     * @ORM\ManyToOne(targetEntity="OrganizationSchedule", inversedBy="organizations")
     * @ORM\JoinColumn(name="organization_schedule", referencedColumnName="id")
     * */
    private $organizationSchedule;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationVisitors", mappedBy="organizations",cascade={"persist"})
     */
    private $organizationVisitors;


    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", mappedBy="organisationFavourite")
     */
    protected $profileFavorite;


    /**
     * One Category has One Vio.
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\TeamMembershipApplication", mappedBy="organization")
     */
    private $membershipApplications;


    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", length=1024, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="text", length=1024, nullable=true)
     */
    private $city;


    /**
     * @return mixed
     */
    public function getProfileFavorite()
    {
        return $this->profileFavorite;
    }


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = 1;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inserates = new ArrayCollection();
        $this->userOrganizations = new ArrayCollection();
        $this->productions = new ArrayCollection();
        $this->qualifications = new ArrayCollection();
        $this->profileFavorite = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->vioDone = new ArrayCollection();
        $this->organizationGrants = new ArrayCollection();
        $this->organizationPerformance = new ArrayCollection();
        $this->organizationVisitors = new ArrayCollection();
        $this->organizationSection = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Organization
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Organization
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Organization
     */
    public function setCity($city)
    {
        $this->city = $city;
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
     * Set description
     *
     * @param string $description
     * @return Organization
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Organization
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
     * Set isVisibleInList
     *
     * @param boolean $isVisibleInList
     * @return Organization
     */
    public function setIsVisibleInList($isVisibleInList)
    {
        $this->isVisibleInList = $isVisibleInList;

        return $this;
    }

    /**
     * Get isVisibleInList
     *
     * @return boolean
     */
    public function getIsVisibleInList()
    {
        return $this->isVisibleInList;
    }

    /**
     * Set isVisibleInRegister
     *
     * @param boolean $isVisibleInRegister
     * @return Organization
     */
    public function setIsVisibleInRegister($isVisibleInRegister)
    {
        $this->isVisibleInRegister = $isVisibleInRegister;

        return $this;
    }

    /**
     * Get isVisibleInRegister
     *
     * @return boolean
     */
    public function getIsVisibleInRegister()
    {
        return $this->isVisibleInRegister;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Organization
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
     * @return Organization
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
     * Add inserates
     *
     * @param \Theaterjobs\InserateBundle\Entity\Inserate $inserates
     * @return Organization
     */
    public function addInserate(\Theaterjobs\InserateBundle\Entity\Inserate $inserates)
    {
        $this->inserates[] = $inserates;

        return $this;
    }

    /**
     * Remove inserates
     *
     * @param \Theaterjobs\InserateBundle\Entity\Inserate $inserates
     */
    public function removeInserate(\Theaterjobs\InserateBundle\Entity\Inserate $inserates)
    {
        $this->inserates->removeElement($inserates);
    }

    /**
     * Get inserates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInserates()
    {
        return $this->inserates;
    }

    /**
     * Set form
     *
     * @param \Theaterjobs\InserateBundle\Entity\FormOfOrganization $form
     * @return Organization
     */
    public function setForm(\Theaterjobs\InserateBundle\Entity\FormOfOrganization $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \Theaterjobs\InserateBundle\Entity\FormOfOrganization
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set address
     *
     * @param AddressInterface $address
     * @return Organization
     */
    public function setAddress(AddressInterface $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return AddressInterface
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Add userOrganizations
     *
     * @param UserOrganizationInterface $userOrganizations
     * @return Organization
     */
    public function addUserOrganization(UserOrganizationInterface $userOrganizations)
    {
        if (!$this->userOrganizations->contains($userOrganizations)) {
            $this->userOrganizations->add($userOrganizations);
        }

        return $this;
    }

    /**
     * Remove userOrganizations
     *
     * @param UserOrganizationInterface $userOrganizations
     */
    public function removeUserOrganization(UserOrganizationInterface $userOrganizations)
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
     * Add adminComments
     *
     * @param \Theaterjobs\InserateBundle\Entity\AdminComments $adminComments
     * @return Organization
     */
    public function addAdminComment(\Theaterjobs\InserateBundle\Entity\AdminComments $adminComments)
    {
        $adminComments->setOrganization($this);
        $comments = $this->adminComments->toArray();
        array_unshift($comments, $adminComments);
        $this->adminComments = $comments;

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
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     * @return Organization
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
     * Add organizationSection
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationSection $organizationSection
     * @return Organization
     */
    public function addOrganizationSection(\Theaterjobs\InserateBundle\Entity\OrganizationSection $organizationSection)
    {
        $this->organizationSection[] = $organizationSection;

        return $this;
    }

    /**
     * Remove organizationSection
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationSection $organizationSection
     */
    public function removeOrganizationSection(\Theaterjobs\InserateBundle\Entity\OrganizationSection $organizationSection)
    {
        $this->organizationSection->removeElement($organizationSection);
    }

    /**
     * Get organizationSection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationSection()
    {
        return $this->organizationSection;
    }

    /**
     * Set organizationKind
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationKind $organizationKind
     * @return Organization
     */
    public function addOrganizationKind(\Theaterjobs\InserateBundle\Entity\OrganizationKind $organizationKind = null)
    {
        $this->organizationKind[] = $organizationKind;

        return $this;
    }

    /**
     * Remove organizatioKind
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationKind $organizationKind
     */
    public function removeOrganizationKind(\Theaterjobs\InserateBundle\Entity\OrganizationKind $organizationKind)
    {
        $this->organizationKind->removeElement($organizationKind);
    }

    /**
     * Get organizationKind
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationKind()
    {
        return $this->organizationKind;
    }

    /**
     * Set organizationGrants
     *
     * @param integer $organizationGrants
     * @return Organization
     */
    public function setOrganizationGrants($organizationGrants)
    {
        $this->organizationGrants = $organizationGrants;

        return $this;
    }

    /**
     * Get organizationGrants
     *
     * @return integer
     */
    public function getOrganizationGrants()
    {
        return $this->organizationGrants;
    }

    /**
     * Set wageFrom
     *
     * @param integer $wageFrom
     * @return Organization
     */
    public function setWageFrom($wageFrom)
    {
        $this->wageFrom = $wageFrom;

        return $this;
    }

    /**
     * Get wageFrom
     *
     * @return integer
     */
    public function getWageFrom()
    {
        return $this->wageFrom;
    }

    /**
     * Set wageTo
     *
     * @param integer $wageTo
     * @return Organization
     */
    public function setWageTo($wageTo)
    {
        $this->wageTo = $wageTo;

        return $this;
    }

    /**
     * Get wageTo
     *
     * @return integer
     */
    public function getWageTo()
    {
        return $this->wageTo;
    }

    /**
     * Set organizationSchedule
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationSchedule $organizationSchedule
     * @return Organization
     */
    public function setOrganizationSchedule(\Theaterjobs\InserateBundle\Entity\OrganizationSchedule $organizationSchedule = null)
    {
        $this->organizationSchedule = $organizationSchedule;

        return $this;
    }

    /**
     * Get organizationSchedule
     *
     * @return \Theaterjobs\InserateBundle\Entity\OrganizationSchedule
     */
    public function getOrganizationSchedule()
    {
        return $this->organizationSchedule;
    }

    /**
     * Add organizationVisitors
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationVisitors
     * @return Organization
     */
    public function addOrganizationVisitor(\Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationVisitors)
    {
        $organizationVisitors->setOrganizations($this);
        $this->organizationVisitors[] = $organizationVisitors;

        return $this;
    }

    /**
     * Remove organizationVisitors
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationVisitors
     */
    public function removeOrganizationVisitor(\Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationVisitors)
    {
        $this->organizationVisitors->removeElement($organizationVisitors);
    }

    /**
     * Get organizationVisitors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationVisitors()
    {
        return $this->organizationVisitors;
    }

    function getOrganizationOwner()
    {
        return $this->organizationOwner;
    }

    function getOrganizationPerformance()
    {
        return $this->organizationPerformance;
    }

    function setOrganizationOwner($organizationOwner)
    {
        $this->organizationOwner = $organizationOwner;
    }

    /**
     * Add organizationPerformance
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationPerformance
     * @return Organization
     */
    public function addOrganizationPerformance(\Theaterjobs\InserateBundle\Entity\OrganizationPerformance $organizationPerformance)
    {
        $organizationPerformance->setOrganizations($this);
        $this->organizationPerformance[] = $organizationPerformance;

        return $this;
    }

    /**
     * Remove organizationPerformance
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationPerformance
     */
    public function removeOrganizationPerformance(\Theaterjobs\InserateBundle\Entity\OrganizationPerformance $organizationPerformance)
    {
        $this->organizationPerformance->removeElement($organizationPerformance);
    }

    function getStaff()
    {
        return $this->staff;
    }

    function setStaff($staff)
    {
        $this->staff = $staff;
    }

    function getOrganizationStage()
    {
        return $this->organizationStage;
    }

    /**
     * Add organizationStage
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationStage
     * @return Organization
     */
    public function addOrganizationStage(\Theaterjobs\InserateBundle\Entity\OrganizationStage $organizationStage)
    {
        $organizationStage->setOrganizations($this);
        $this->organizationStage[] = $organizationStage;

        return $this;
    }

    /**
     * Remove organizationStage
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationVisitors $organizationStage
     */
    public function removeOrganizationStage(\Theaterjobs\InserateBundle\Entity\OrganizationStage $organizationStage)
    {
        $this->organizationStage->removeElement($organizationStage);
    }

    function getOrganizationEnsemble()
    {
        return $this->organizationEnsemble;
    }

    /**
     * Add organizationEnsemble
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble
     *
     * @return Organization
     */
    public function addOrganizationEnsemble(\Theaterjobs\InserateBundle\Entity\OrganizationEnsemble $organizationEnsemble)
    {
        $this->organizationEnsemble[] = $organizationEnsemble;
        $organizationEnsemble->setOrganization($this);

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
     * Add organizationStaff
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationStaff $organizationStaff
     *
     * @return Organization
     */
    public function addOrganizationStaff(\Theaterjobs\InserateBundle\Entity\OrganizationStaff $organizationStaff)
    {
        $this->organizationStaff[] = $organizationStaff;
        $organizationStaff->setOrganization($this);

        return $this;
    }

    /**
     * Remove organizationStaff
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationStaff $organizationStaff
     */
    public function removeOrganizationStaff(\Theaterjobs\InserateBundle\Entity\OrganizationStaff $organizationStaff)
    {
        $this->organizationStaff->removeElement($organizationStaff);
    }

    /**
     * Get organizationStaff
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationStaff()
    {
        return $this->organizationStaff;
    }

    /**
     * Set destroyedAt
     *
     * @param \DateTime $destroyedAt
     *
     * @return Organization
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
     * Set notReachableAt
     *
     * @param \DateTime $notReachableAt
     *
     * @return Organization
     */
    public function setNotReachableAt($notReachableAt)
    {
        $this->notReachableAt = $notReachableAt;

        return $this;
    }

    /**
     * Get notReachableAt
     *
     * @return \DateTime
     */
    public function getNotReachableAt()
    {
        return $this->notReachableAt;
    }

    /**
     * Add organizationGrant
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationGrants $organizationGrant
     *
     * @return Organization
     */
    public function addOrganizationGrant(\Theaterjobs\InserateBundle\Entity\OrganizationGrants $organizationGrant)
    {
        $organizationGrant->setOrganizations($this);
        $this->organizationGrants[] = $organizationGrant;

        return $this;
    }

    /**
     * Remove organizationGrant
     *
     * @param \Theaterjobs\InserateBundle\Entity\OrganizationGrants $organizationGrant
     */
    public function removeOrganizationGrant(\Theaterjobs\InserateBundle\Entity\OrganizationGrants $organizationGrant)
    {
        $this->organizationGrants->removeElement($organizationGrant);
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
     * Set orchestraClass
     *
     * @param string $orchestraClass
     *
     * @return Organization
     */
    public function setOrchestraClass($orchestraClass)
    {
        $this->orchestraClass = $orchestraClass;

        return $this;
    }

    /**
     * Get orchestraClass
     *
     * @return string
     */
    public function getOrchestraClass()
    {
        return $this->orchestraClass;
    }

    /**
     * @return unknown
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param unknown $subdir
     * @return Organization
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }

    /**
     * @return File
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    /**
     * @param File $uploadFile
     * @return Organization
     */
    public function setUploadFile($uploadFile)
    {
        $this->uploadFile = $uploadFile;

        if ($uploadFile instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Organization
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get contactSection
     *
     * @return \Theaterjobs\InserateBundle\Entity\ContactSection
     */
    public function getContactSection()
    {
        return $this->contactSection;
    }

    /**
     * Set contactSection
     *
     * @param \Theaterjobs\InserateBundle\Entity\ContactSection $contactSection
     *
     * @return Organization
     */
    public function setContactSection(ContactSection $contactSection = null)
    {
        $this->contactSection = $contactSection;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Organization
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganisationApplicationInfoText()
    {
        return $this->organisationApplicationInfoText;
    }

    /**
     * @param string $organisationApplicationInfoText
     * @return Organization
     */
    public function setOrganisationApplicationInfoText($organisationApplicationInfoText)
    {
        $this->organisationApplicationInfoText = $organisationApplicationInfoText;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrganisationApplicationInfoDate()
    {
        return $this->organisationApplicationInfoDate;
    }

    /**
     * @param \DateTime $organisationApplicationInfoDate
     * @return Organization
     */
    public function setOrganisationApplicationInfoDate($organisationApplicationInfoDate)
    {
        $this->organisationApplicationInfoDate = $organisationApplicationInfoDate;
        return $this;
    }


    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Experience", mappedBy="organization")
     */
    private $experiences;

    /**
     * Add experience
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Experience $experience
     *
     * @return Organization
     */
    public function addExperience(Experience $experience)
    {
        $this->experiences[] = $experience;

        return $this;
    }

    /**
     * Remove experience
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Experience $experience
     */
    public function removeExperience(Experience $experience)
    {
        $this->experiences->removeElement($experience);
    }

    /**
     * Get $experience
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExperience()
    {
        return $this->experiences;
    }


    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Production", mappedBy="organizationRelated")
     */
    private $productions;

    /**
     * Add production
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Production $production
     *
     * @return Organization
     */
    public function addProduction(Production $production)
    {
        $this->productions[] = $production;

        return $this;
    }

    /**
     * Remove production
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Production $production
     */
    public function removeProduction(Production $production)
    {
        $this->productions->removeElement($production);
    }

    /**
     * Get production
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProduction()
    {
        return $this->productions;
    }

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Qualification", mappedBy="organizationRelated")
     */
    private $qualifications;

    /**
     * Add qualification
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Qualification $qualification
     *
     * @return Organization
     */
    public function addQualification(Qualification $qualification)
    {
        $this->qualifications[] = $qualification;

        return $this;
    }

    /**
     * Remove qualification
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Qualification $qualification
     */
    public function removeQualification(Qualification $qualification)
    {
        $this->qualifications->removeElement($qualification);
    }

    /**
     * Get qualification
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQualification()
    {
        return $this->qualifications;
    }

    /**
     * One organization can have more than .
     * @ORM\OneToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="merge_to_id", referencedColumnName="id")
     */
    private $mergedTo;

    /**
     * @return mixed
     */
    public function getMergedTo()
    {
        return $this->mergedTo;
    }

    /**
     * @param mixed $mergedTo
     */
    public function setMergedTo($mergedTo)
    {
        $this->mergedTo = $mergedTo;
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
     * @return Organization
     */
    public function setVioDone($vioDone)
    {
        $this->vioDone = $vioDone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVio()
    {
        return $this->vio;
    }

    /**
     * @param mixed $vio
     * @return Organization
     */
    public function setVio($vio)
    {
        $this->vio = $vio;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getMembershipApplication()
    {
        return $this->membershipApplications;
    }

    /**
     * @param mixed $membershipApplications
     * @return Organization
     */
    public function setMembershipApplication($membershipApplications)
    {
        $this->membershipApplications = $membershipApplications;
        return $this;
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
     * @return Organization
     */
    public function setStatusChangedAt($statusChangedAt)
    {
        $this->statusChangedAt = $statusChangedAt;
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
     * @return Organization
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Check if a user is part of this organization
     *
     * @param $user
     * @return bool
     */
    public function isTeamMember(UserInterface $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->userOrganizations->exists(function ($key, $element) use ($user) {
            return $element->getUser()->getId() == $user->getId() && !$element->getRevokedAt() && $element->getGrantedAt();
        });
    }

    /**
     * get user organization
     *
     * @param $user
     * @return \Theaterjobs\UserBundle\Entity\UserOrganization
     */
    public function getTeamMember(User $user)
    {
        $userOrganization = $this->userOrganizations->filter(
            function ($element) use ($user) {
                return $element->getUser()->getId() == $user->getId();
            }
        );

        return $userOrganization->current();

    }


    /**
     * Check if a user is part of this organization
     *
     * @return bool
     */
    public function isOrchestra()
    {
        return $this->organizationSection->exists(function ($key, $element) {
            return $element->getId() == self::SECTION_ORCHESTRA;
        });
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == self::ACTIVE;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() == self::PENDING;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->getStatus() == self::UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->getStatus() == self::CLOSED;
    }

    /**
     * @return bool
     */
    public function isActiveOrClosed()
    {
        return $this->isActive() || $this->isClosed();
    }


}
