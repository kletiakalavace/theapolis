<?php

namespace Theaterjobs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;
use Theaterjobs\UserBundle\Entity\User;

/**
 * SepaXmlBilling
 *
 * @ORM\Table("sepa_xml_billing")
 * @ORM\Entity(repositoryClass="Theaterjobs\AdminBundle\Entity\SepaXmlBillingRepository")
 */
class SepaXmlBilling
{
    const PATHDIR = "/../web/uploads/sepa-xml/";

    use Timestampable;

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
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $fileName;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Theaterjobs\UserBundle\Entity\User", inversedBy="sepaXmlBillings")
     * @ORM\JoinColumn(name="tj_user_id", referencedColumnName="id", nullable=true)
     */
    private $lastDownloadedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_downloaded_at", type="datetime", nullable=true)
     */
    private $lastDownloadedAt;


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
     * Set fileName
     *
     * @param string $fileName
     *
     * @return SepaXmlBilling
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set $lastDownloadedBy
     *
     * @param User $lastDownloadedBy
     *
     * @return SepaXmlBilling
     */
    public function setLastDownloadedBy($lastDownloadedBy)
    {
        $this->lastDownloadedBy = $lastDownloadedBy;

        return $this;
    }

    /**
     * Get lastDownloadedBy
     *
     * @return User
     */
    public function getLastDownloadedBy()
    {
        return $this->lastDownloadedBy;
    }

    /**
     * Set lastDownloadedAt
     *
     * @param \DateTime $lastDownloadedAt
     *
     * @return SepaXmlBilling
     */
    public function setLastDownloadedAt($lastDownloadedAt)
    {
        $this->lastDownloadedAt = $lastDownloadedAt;

        return $this;
    }

    /**
     * Get lastDownloadedAt
     *
     * @return \DateTime
     */
    public function getLastDownloadedAt()
    {
        return $this->lastDownloadedAt;
    }
}

