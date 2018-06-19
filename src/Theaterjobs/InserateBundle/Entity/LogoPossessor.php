<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_inserate_logo_possessor")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"tj_inserate_organizations" = "Organization" , "tj_inserate_inserates" = "Inserate" })
 * @ORM\HasLifecycleCallbacks
 *
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
abstract class LogoPossessor
{

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'logos';

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
     * @return string
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param string $temp
     * @return LogoPossessor
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
        return $this;
    }

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="logo", fileNameProperty="path")
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
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

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
     * @return string
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param unknown $subdir
     * @return LogoPossessor
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
     * @return LogoPossessor
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
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return LogoPossessor
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
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
