<?php

namespace Theaterjobs\NewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Theaterjobs\NewsBundle\Model\OrganizationInterface;
use Theaterjobs\NewsBundle\Model\ProfileInterface;
use Theaterjobs\StatsBundle\Model\ViewableInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * News
 *
 * @ORM\Table(name="tj_news")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\NewsBundle\Entity\NewsRepository"
 * )
 * @Vich\Uploadable
 */
class News implements ViewableInterface
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
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'image';

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\NewsBundle\Model\ProfileInterface")
     * @ORM\JoinTable(name="tj_news_users")
     * */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="pretitle", type="string", length=255)
     */
    protected $pretitle;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text")
     */
    protected $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_at", type="datetime", nullable=true)
     */
    protected $publishAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="un_publish_at", type="datetime", nullable=true)
     */
    protected $unPublishAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\NewsBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    protected $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="Replies", mappedBy="news", cascade={"persist","remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $replies;

    /**
     * @ORM\ManyToMany(targetEntity="Tags", inversedBy="news", cascade={"persist"})
     * @ORM\JoinTable(name="tj_news_tags")
     * */
    protected $tags;

    /**
     * Unidirectional
     * @ORM\ManyToMany(targetEntity="Theaterjobs\NewsBundle\Model\OrganizationInterface")
     * @ORM\JoinTable(name="tj_news_organizations")
     * */
    protected $organizations;

    /**
     * @var boolean
     * @ORM\Column(name="published",type="boolean")
     */
    protected $published = false;

    /**
     * @var integer
     * @ORM\Column(name="total_views",type="integer", nullable=true)
     */
    protected $totalViews = 0;

    /**
     * @var boolean
     * @ORM\Column(name="archived",type="boolean")
     */
    protected $archived = false;

    /**
     * @Gedmo\Slug(
     *     fields={"title"}, updatable=true, unique=true
     * )
     * separator (optional, default="-")
     * style (optional, default="default") - "default" all letters will be lowercase
     * @ORM\Column(name="slug", length=128)
     */
    private $slug;

    /**
     * @var integer
     *
     * @ORM\Column(name="published_comments", type="integer")
     */
    private $publishedComments = 0;

    /**
     * @ORM\Column(name="geolocation",type="string", length=255 ,nullable=true)
     */
    protected $geolocation;


    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="news", fileNameProperty="path")
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
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", mappedBy="newsFavourite")
     */
    protected $profileFavourites;

    /**
     * @return mixed
     */
    public function getProfileFavourites()
    {
        return $this->profileFavourites;
    }

    /**
     * @param mixed $profileFavourites
     * @return News
     */
    public function addProfileFavourites(Profile $profileFavourites)
    {
        $this->profileFavourites[] = $profileFavourites;
        return $this;
    }

    public function removeProfileFavourites(Profile $profileFavourites)
    {
        $this->profileFavourites->removeElement($profileFavourites);
    }

    /**
     *
     * @var string
     */
    protected $temp;

    /**
     * @return string
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param string $temp
     * @return News
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
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
     * @param $uploadFile
     * @return News
     * @internal param File $uploadFile
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
     * @return News
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $path;

    /**
     *
     * @var string
     * @ORM\Column(name="image_description", type="string", length=255, nullable=true)
     */
    protected $imageDescription;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->replies = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->profileFavourites = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return News
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
     * Set pretitle
     *
     * @param string $pretitle
     * @return News
     */
    public function setPretitle($pretitle)
    {
        $this->pretitle = $pretitle;

        return $this;
    }

    /**
     * Get pretitle
     *
     * @return string
     */
    public function getPretitle()
    {
        return $this->pretitle;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     * @return News
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return News
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
     * @return unknown
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param unknown $subdir
     * @return News
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return News
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Add replies
     *
     * @param \Theaterjobs\NewsBundle\Entity\Replies $replies
     * @return News
     */
    public function addReply(\Theaterjobs\NewsBundle\Entity\Replies $replies)
    {
        $this->replies[] = $replies;

        return $this;
    }

    /**
     * Remove replies
     *
     * @param \Theaterjobs\NewsBundle\Entity\Replies $replies
     */
    public function removeReply(\Theaterjobs\NewsBundle\Entity\Replies $replies)
    {
        $this->replies->removeElement($replies);
    }

    /**
     * Get replies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * (non-PHPdoc)
     * @see LogoPossessor::getType()
     *
     * @return type of the LogoPossessor
     */
    public function getType()
    {
        return 'tj_news';
    }

    /**
     * Add organization
     *
     * @param OrganizationInterface $organization
     * @return News
     */
    public function addOrganization(OrganizationInterface $organization)
    {
        $this->organizations[] = $organization;

        return $this;
    }

    /**
     * Remove organization
     *
     * @param OrganizationInterface $organization
     */
    public function removeOrganization(OrganizationInterface $organization)
    {
        $this->organizations->removeElement($organization);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return News
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
     * Add users
     *
     * @param ProfileInterface $users
     * @return News
     */
    public function addUser($users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param ProfileInterface $users
     */
    public function removeUser($users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    function getPublished()
    {
        return $this->published;
    }

    function setPublished($published)
    {
        $this->published = $published;
    }

    function getPublishAt()
    {
        return $this->publishAt;
    }

    function setPublishAt($publishAt)
    {
        $this->publishAt = $publishAt;
    }

    /**
     * Set createdBy
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $createdBy
     * @return News
     */
    public function setCreatedBy(Profile $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    function getArchived()
    {
        return $this->archived;
    }

    function setArchived($archived)
    {
        $this->archived = $archived;
    }

    /**
     * Add tags
     *
     * @param \Theaterjobs\NewsBundle\Entity\Tags $tags
     * @return News
     */
    public function addTag(\Theaterjobs\NewsBundle\Entity\Tags $tags)
    {
        $this->tags[] = $tags;
        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Theaterjobs\NewsBundle\Entity\Tags $tags
     */
    public function removeTag(\Theaterjobs\NewsBundle\Entity\Tags $tags)
    {
        $this->tags->removeElement($tags);
    }


    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getPublishedComments()
    {
        return $this->publishedComments;
    }

    function setPublishedComments($publishedComments)
    {
        $this->publishedComments = $publishedComments;
    }

    /**
     * Set geolocation
     *
     * @param string $geolocation
     *
     * @return News
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return News
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


    function getImageDescription()
    {
        return $this->imageDescription;
    }

    function setImageDescription($imageDescription)
    {
        $this->imageDescription = $imageDescription;
    }

    /**
     * @return \DateTime
     */
    public function getUnPublishAt()
    {
        return $this->unPublishAt;
    }

    /**
     * @param \DateTime $unPublishAt
     * @return News
     */
    public function setUnPublishAt($unPublishAt)
    {
        $this->unPublishAt = $unPublishAt;
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
     * Set totalViews
     *
     * @param integer $totalViews
     *
     * @return News
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
     * Add profileFavourite
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profileFavourite
     *
     * @return News
     */
    public function addProfileFavourite(\Theaterjobs\ProfileBundle\Entity\Profile $profileFavourite)
    {
        $this->profileFavourites[] = $profileFavourite;

        return $this;
    }

    /**
     * Remove profileFavourite
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profileFavourite
     */
    public function removeProfileFavourite(\Theaterjobs\ProfileBundle\Entity\Profile $profileFavourite)
    {
        $this->profileFavourites->removeElement($profileFavourite);
    }
}
