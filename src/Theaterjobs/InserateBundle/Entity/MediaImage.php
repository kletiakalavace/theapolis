<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_inserate_uploaded_images")
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 */
class MediaImage extends UploadedMedia
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'images';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Inserate", inversedBy="mediaImage")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $inserate;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="inserate", fileNameProperty="path")
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
     * @return unknown
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param unknown $subdir
     * @return MediaImage
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
     * @param $uploadFile
     * @return MediaImage
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

    function getInserate()
    {
        return $this->inserate;
    }

    function setInserate($inserate)
    {
        $this->inserate = $inserate;
    }

    public function getType()
    {
        return 'tj_inserate_photos';
    }


    /**
     * Set title
     *
     * @param string $title
     *
     * @return MediaImage
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
}
