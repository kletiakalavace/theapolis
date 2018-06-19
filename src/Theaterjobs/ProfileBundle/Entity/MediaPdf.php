<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_profile_uploaded_pdf")
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 */
class MediaPdf extends UploadedMedia
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'pdf';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="mediaPdf")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $profile;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    protected $title;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="profile", fileNameProperty="path")
     *
     * @Assert\File(
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     maxSize = "5M",
     * )
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
     * @return MediaPdf
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
     * @return MediaPdf
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

    public function getType()
    {
        return 'tj_profile_profile_pdf';
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return MediaPdf
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
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
     * used for file system privacy check route "tj_pdf_file"
     * @return string
     */
    public function getUploadableField()
    {
        return 'uploadFile';
    }

}
