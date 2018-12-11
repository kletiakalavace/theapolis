<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_inserate_uploaded_pdf")
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
     * @ORM\ManyToOne(targetEntity="Inserate", inversedBy="mediaPdf")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $inserate;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    protected $title;

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

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="inserate", fileNameProperty="path")
     *
     * @Assert\File(
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     maxSize = "5M",
     * )
     *
     * @var File
     */
    protected $uploadFile;

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return 'tj_profile_profile_pdf';
    }

    function getInserate()
    {
        return $this->inserate;
    }

    function setInserate($inserate)
    {
        $this->inserate = $inserate;
    }


}
