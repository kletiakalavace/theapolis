<?php

namespace Theaterjobs\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SiteInfoMedia
 *
 * @ORM\Table("tj_admin_site_info_media")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SiteInfoMedia {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne( targetEntity="SiteInfo", inversedBy="siteInfoMedia")
     * @ORM\JoinColumn(name="site_info_id", referencedColumnName="id", nullable=true)
     */
    private $siteInfo;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    /**
     *
     * @var string
     */
    protected $temp;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return SiteInfoMedia
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return SiteInfoMedia
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return SiteInfoMedia
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SiteInfoMedia
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set siteInfo
     *
     * @param \Theaterjobs\MainBundle\Entity\SiteInfo $siteInfo
     *
     * @return SiteInfoMedia
     */
    public function setSiteInfo(\Theaterjobs\MainBundle\Entity\SiteInfo $siteInfo = null) {
        $this->siteInfo = $siteInfo;

        return $this;
    }

    /**
     * Get siteInfo
     *
     * @return \Theaterjobs\MainBundle\Entity\SiteInfo
     */
    public function getSiteInfo() {
        return $this->siteInfo;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
        // check if we have an old image path
        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Get the absolute path
     *
     * @return Ambigous <NULL, string>
     */
    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . $this->path;
    }

    /**
     * Get the web path.
     *
     * @return Ambigous <NULL, string>
     */
    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . $this->path;
    }

    /**
     * Get the upload root dir.
     *
     * @return string
     */
    protected function getUploadRootDir() {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web' . $this->getUploadDir();
    }

    /**
     * Get the upload dir.
     *
     * @return string
     */
    protected function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/uploads/siteinfo/' . $this->getSiteInfo()->getId() . '/';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->getFile()) {
            $suffix = array();
            preg_match("/(^.+)?(\..+$)/", $this->getFile()->getClientOriginalName(), $suffix);
            if (count($suffix)) {
                $this->path = $suffix[1] . "." . $this->getFile()->guessExtension();
            } else {
                $this->path = "logo." . $this->getFile()->guessExtension();
            }
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->getFile()) {
            return;
        }

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->temp);
            // clear the temp image path
            $this->temp = null;
        }

        $this->getFile()->move($this->getUploadRootDir(), $this->path);
        $this->setFile(null);
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove() {
        $this->temp = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if (isset($this->temp)) {
            unlink($this->temp);
        }
    }

}