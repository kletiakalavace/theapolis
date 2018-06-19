<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_profile_uploaded_media")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"parent" = "UploadedMedia", "tj_profile_profile_photos" = "MediaImage","tj_profile_profile_audios"="MediaAudio","tj_profile_profile_pdf"="MediaPdf"})
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
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;


    /**
     * @var integer
     * @ORM\Column( name="size", type="integer",nullable=true)
     */
    protected $size;

//    /**
//     * @var boolean
//     * @ORM\Column(name="copyright",type="boolean")
//     */
//    protected $copyright;


    /**
     * @var integer
     * @ORM\Column(name="position",type="integer",nullable=true)
     */
    protected $position;

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return UploadedMedia
     */
    public function setPosition($position)
    {
        $this->position = $position;
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

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

//    public function getCopyright()
//    {
//        return $this->copyright;
//    }
//
//    public function setCopyright($copyright)
//    {
//        $this->copyright = $copyright;
//    }

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

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return UploadedMedia
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
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
