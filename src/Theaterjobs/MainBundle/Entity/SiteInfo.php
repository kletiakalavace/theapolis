<?php

namespace Theaterjobs\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiteInfo
 *
 * @ORM\Table("tj_admin_site_info")
 * @ORM\Entity()
 */
class SiteInfo {

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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Model\UserInterface", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * */
    protected $user;

    /**
     * @ORM\OneToMany( targetEntity="SiteInfoMedia", mappedBy="siteInfo", cascade={"persist", "remove"})
     * */
    private $siteInfoMedia;

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
     * @return SiteInfo
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
     * @return SiteInfo
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SiteInfo
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return SiteInfo
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return SiteInfo
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return SiteInfo
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param \Theaterjobs\UserBundle\Entity\User $user
     *
     * @return SiteInfo
     */
    public function setUser(\Theaterjobs\UserBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Theaterjobs\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->siteInfoMedia = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add siteInfoMedia
     *
     * @param \Theaterjobs\MainBundle\Entity\SiteInfoMedia $siteInfoMedia
     *
     * @return SiteInfo
     */
    public function addSiteInfoMedion(\Theaterjobs\MainBundle\Entity\SiteInfoMedia $siteInfoMedia) {
        $siteInfoMedia->setSiteInfo($this);

        $this->siteInfoMedia[] = $siteInfoMedia;

        return $this;
    }

    /**
     * Remove siteInfoMedia
     *
     * @param \Theaterjobs\MainBundle\Entity\SiteInfoMedia $siteInfoMedia
     */
    public function removeSiteInfoMedion(\Theaterjobs\MainBundle\Entity\SiteInfoMedia $siteInfoMedia) {
        $this->siteInfoMedia->removeElement($siteInfoMedia);
    }

    /**
     * Get siteInfoMedia
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSiteInfoMedia() {
        return $this->siteInfoMedia;
    }

}