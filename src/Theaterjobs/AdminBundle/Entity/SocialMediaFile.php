<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SocialMediaFile
 *
 * @ORM\Table(name="tj_social_media_file")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\SocialMediaFileRepository")
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 *
 */
class SocialMediaFile
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
     * The Discriminator-Map is defined in the parent class.
     * @var string
     */
    protected $subdir = 'icons';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    protected $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="socialMedia", fileNameProperty="path")
     *
     * @Assert\File(
     *    mimeTypes = "text/plain",
     *    maxSize = "5M",
     * )
     * @var File
     */
    protected $uploadFile;

    /**
     * One SocialMediaFile has One SocialMedia.
     * @ORM\OneToMany(targetEntity="SocialMedia", mappedBy="mediaFile")
     */
    private $socialMedia;

    /**
     * @return File
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    /**
     * @param $uploadFile
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
     * @return SocialMediaFile
     */
    public function setPath($path)
    {
        $this->path = $path;
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
     * @return mixed
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param mixed $subdir
     * @return SocialMediaFile
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
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
     * @return SocialMediaFile
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * @param mixed $socialMedia
     * @return SocialMediaFile
     */
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;
        return $this;
    }
}

