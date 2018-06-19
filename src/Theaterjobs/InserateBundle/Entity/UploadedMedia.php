<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_inserate_uploaded_media")
 * @ORM\Entity(repositoryClass="UploadedMediaRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"parent" = "UploadedMedia", "tj_inserate_photos" = "MediaImage","tj_inserate_audios"="MediaAudio","tj_inserate_pdf"="MediaPdf"})
 * @ORM\HasLifecycleCallbacks
 *
 * @category Entity
 * @package  Theaterjobs\ProfileBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
abstract class UploadedMedia
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'media';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     *
     * @var string
     */
    protected $temp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return UploadedMedia
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

//    /**
//     * @Assert\File(maxSize="6000000")
//     */
//    protected $file;

//     /**
//     * Get the absolute path
//     *
//     * @return Ambigous <NULL, string>
//     */
//    public function getAbsolutePath() {
//        return null === $this->path ? null : $this->getUploadRootDir() . $this->path;
//    }
//
//    /**
//     * Get the web path.
//     *
//     * @return Ambigous <NULL, string>
//     */
//    public function getLogoWebPath() {
//        return null === $this->path ? null : $this->getUploadDir() . $this->path;
//    }
//
//    /**
//     * Get the upload root dir.
//     *
//     * @return string
//     */
//    protected function getUploadRootDir() {
//        // the absolute directory path where uploaded
//        // documents should be saved
//        return __DIR__ . '/../../../../web' . $this->getUploadDir();
//    }
//
//    /**
//     * Get the upload dir.
//     *
//     * @return string
//     */
//    protected function getUploadDir() {
//        // get rid of the __DIR__ so it doesn't screw up
//        // when displaying uploaded doc/image in the view.
//        return '/uploads/logos/' . $this->subdir . '/' . $this->id . '/';
//    }

//    /**
//     * Sets file.
//     *
//     * @param UploadedFile $file
//     */
//    public function setFile(UploadedFile $file = null) {
//        $this->file = $file;
//        // check if we have an old image path
//        if (is_file($this->getAbsolutePath())) {
//            // store the old name to delete after the update
//            $this->temp = $this->getAbsolutePath();
//        } else {
//            $this->path = 'initial';
//        }
//    }
//
//    /**
//     * Get file.
//     *
//     * @return UploadedFile
//     */
//    public function getFile() {
//        return $this->file;
//    }
//
//    /**
//     * @ORM\PrePersist()
//     * @ORM\PreUpdate()
//     */
//    public function preUpload() {
//        if (null !== $this->getFile()) {
//            $suffix = array();
//            preg_match("/(^.+)?(\..+$)/", $this->getFile()->getClientOriginalName(), $suffix);
//            if (count($suffix)) {
//                $this->path = $suffix[1] . "." . $this->getFile()->guessExtension();
//            } else {
//                $this->path = "logo." . $this->getFile()->guessExtension();
//            }
//        }
//    }
//
//    /**
//     * @ORM\PostPersist()
//     * @ORM\PostUpdate()
//     */
//    public function upload() {
//
//        if (null === $this->getFile()) {
//            return;
//        }
//
//        // check if we have an old image
//        if (isset($this->temp)) {
//            // delete the old image
//            unlink($this->temp);
//            // clear the temp image path
//            $this->temp = null;
//        }
//
//        // you must throw an exception here if the file cannot be moved
//        // so that the entity is not persisted to the database
//        // which the UploadedFile move() method does
//        $this->getFile()->move(
//            $this->getUploadRootDir(), $this->path
//        );
//
//        $this->setFile(null);
//    }
//
//    /**
//     * @ORM\PreRemove()
//     */
//    public function storeFilenameForRemove() {
//        $this->temp = $this->getAbsolutePath();
//    }
//
//    /**
//     * @ORM\PostRemove()
//     */
//    public function removeUpload() {
//        if (isset($this->temp)) {
//            unlink($this->temp);
//        }
//    }

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
     * Set path
     *
     * @param string $path
     *
     * @return LogoPossessor
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the type of the LogoPossessor.
     * It must reference the discriminator-names shown in the
     * top annotation for doctrine!
     * @TODO It would be much better to reference the discr-field itself but
     *       i don't know howto do that
     */
    abstract public function getType();

}