<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_profile_uploaded_images")
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 */
class MediaImage extends UploadedMedia
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var string
     */
    protected $subdir = 'image';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean
     * @ORM\Column(name="is_profile_photo", type="boolean")
     */
    protected $isProfilePhoto = 0;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=128)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="data_filter", type="string", length=128, nullable=true)
     */
    protected $filter = '';

    /**
     * @var string
     * @ORM\Column(name="copyright_text", type="string", length=255, nullable=true)
     */
    protected $copyrightText = '';

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="mediaImage")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $profile;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="profile", fileNameProperty="path")
     *
     * @Assert\Image(
     *     mimeTypes = "image/*",
     *     maxSize = "10M",
     * )
     * @var File
     */
    protected $uploadFile;

    /**
     * @return string
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param string $subdir
     * @return MediaImage
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     * @return MediaImage
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
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
     * @return MediaImage
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return MediaImage
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return MediaImage
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return MediaImage
     */
    public function setPosition($position)
    {
        $this->position = $position;
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
     * @return void
     * @internal param File $uploadFile
     */
    public function setUploadFile($uploadFile)
    {
        $this->uploadFile = $uploadFile;

        if ($uploadFile instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIsProfilePhoto()
    {
        return $this->isProfilePhoto;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setIsProfilePhoto($isProfilePhoto)
    {
        $this->isProfilePhoto = $isProfilePhoto;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getType()
    {
        return 'tj_profile_profile_photos';
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateSpaceUsed()
    {
//@todo commented by Jana because I had trouble with migration here
//        $space = $this->getProfile()->getUsedSpace();
//        $size = $this->getSize();
//        if ($space + $size < $this->getProfile()->getLimit()) {
//            $this->getProfile()->setUsedSpace($space + $size);
//        } else {
//            throw new UploadException('Reach maximum file quota ' . (($this->getProfile()->getLimit() / 1024) / 1024) . ' MB', 200);
//        }
    }

    /**
     * @ORM\PreRemove
     */
    public function removeSpaceUsed()
    {
        $space = $this->getProfile()->getUsedSpace();
        $size = $this->getSize();
        $this->getProfile()->setUsedSpace($space - $size);
    }

    /**
     * used for file system privacy check route "tj_image_file"
     * @return string
     */
    public function getImageUploadableField()
    {
        return 'uploadFile';
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
     * @return MediaImage
     */
    public function setCopyrightText($copyrightText)
    {
        $this->copyrightText = $copyrightText;
        return $this;
    }


}
