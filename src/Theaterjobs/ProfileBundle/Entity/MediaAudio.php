<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for the gratification.
 * @ORM\Table(name="tj_profile_uploaded_audios")
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 */
class MediaAudio extends UploadedMedia
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'audio';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="mediaAudio")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $profile;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Assert\Length(
     *      min = 1,
     *      max = 60,
     *      minMessage = "Your title name must be at least {{ limit }} characters long",
     *      maxMessage = "Your title name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="pathImage", type="string", length=255, nullable=true)
     */
    protected $pathImage;

    /**
     * @var string
     *
     * @ORM\Column(name="copyright_text", type="string", length=255, nullable=true)
     */
    protected $copyrightText;


    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="profile", fileNameProperty="path")
     *
     * @Assert\File(
     *     mimeTypes = "audio/*",
     *     maxSize = "10M",
     * )
     * @var File
     */
    protected $uploadFile;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="profile", fileNameProperty="pathImage")
     *
     * @Assert\Image(
     *     mimeTypes = "image/*",
     *     maxSize = "10M",
     * )
     * @var File
     */
    protected $uploadFileImage;

    /**
     * @return File
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    /**
     * @param $uploadFile
     * @return MediaAudio
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
     * @return File
     */
    public function getUploadFileImage()
    {
        return $this->uploadFileImage;
    }

    /**
     * @param $uploadFileImage
     * @return MediaAudio
     * @internal param File $uploadFile
     */
    public function setUploadFileImage($uploadFileImage)
    {
        $this->uploadFileImage = $uploadFileImage;

        if ($uploadFileImage instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTime());
        }
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
     * @return MediaAudio
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return 'tj_profile_profile_audios';
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

    function getProfile()
    {
        return $this->profile;
    }

    function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * used for file system privacy check route "tj_audio_image_file"
     * @return string
     */
    public function getImageUploadableField()
    {
        return 'uploadFileImage';
    }

    /**
     * used for file system privacy check route "tj_audio_file"
     * @return string
     */
    public function getUploadableField()
    {
        return 'uploadFile';
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return MediaAudio
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * @return string
     */
    public function getPathImage()
    {
        return $this->pathImage;
    }

    /**
     * @param string $pathImage
     * @return MediaAudio
     */
    public function setPathImage($pathImage)
    {
        $this->pathImage = $pathImage;
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
     * @return MediaAudio
     */
    public function setCopyrightText($copyrightText)
    {
        $this->copyrightText = $copyrightText;
        return $this;
    }


}
