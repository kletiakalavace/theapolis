<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SocialMedia
 *
 * @ORM\Table(name="tj_social_media")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\SocialMediaRepository")
 * @UniqueEntity(fields={"name"}, message="This entity already exists!")
 *
 */
class SocialMedia
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotNull(message="This field cant be empty!")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="svg_name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotNull(message="This field cant be empty!")
     */
    private $svgName;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * One SocialMedia has One SocialMediaFile.
     * @ORM\ManyToOne(targetEntity="SocialMediaFile", inversedBy="socialMedia", cascade={"persist","remove"})
     */
    private $mediaFile;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\ProfileSocialMedia" , mappedBy="socialMedia")
     */
    private $profileMedia;

    /**
     * @ORM\OneToMany(targetEntity="Theaterjobs\InserateBundle\Entity\OrganizationSocialMedia" , mappedBy="socialMedia")
     */
    private $organizationMedia;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profileMedia = new ArrayCollection();
        $this->organizationMedia = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getProfileMedia()
    {
        return $this->profileMedia;
    }

    /**
     * @param mixed $profileMedia
     * @return SocialMedia
     */
    public function setProfileMedia($profileMedia)
    {
        $this->profileMedia = $profileMedia;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganizationMedia()
    {
        return $this->organizationMedia;
    }

    /**
     * @param mixed $organizationMedia
     * @return SocialMedia
     */
    public function setOrganizationMedia($organizationMedia)
    {
        $this->organizationMedia = $organizationMedia;
        return $this;
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
     *
     * @return SocialMedia
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Set position
     *
     * @param integer $position
     *
     * @return SocialMedia
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function getMediaFile()
    {
        return $this->mediaFile;
    }

    /**
     * @param mixed $mediaFile
     * @return SocialMedia
     */
    public function setMediaFile($mediaFile)
    {
        $this->mediaFile = $mediaFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSvgName()
    {
        return $this->svgName;
    }

    /**
     * @param mixed $svgName
     * @return SocialMedia
     */
    public function setSvgName($svgName)
    {
        $this->svgName = $svgName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return SocialMedia
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return SocialMedia
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}

