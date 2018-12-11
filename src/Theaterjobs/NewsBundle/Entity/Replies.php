<?php

namespace Theaterjobs\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Replies
 *
 * @ORM\Table(name="tj_news_replies")
 * @ORM\Entity()
 */
class Replies {

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
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="News" ,inversedBy="replies", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="news_id", referencedColumnName="id")
     */
    private $news;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checked_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $checkedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\NewsBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="checked_by", referencedColumnName="id")
     */
    protected $checkedBy;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\NewsBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="posted_by", referencedColumnName="id")
     */
    private $profile;

    /**
     * @var boolean
     * @ORM\Column(name="use_forum_alias",type="boolean", nullable=true)
     */
    private $useForumAlias;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archivedAt", type="datetime", nullable=true)
     */
    protected $archivedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Replies
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Replies
     */
    public function setDate($date) {
        $this->date = new \DateTime();

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Replies
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
     * Set checkedAt
     *
     * @param \DateTime $checkedAt
     * @return Replies
     */
    public function setCheckedAt($checkedAt) {
        $this->checkedAt = $checkedAt;

        return $this;
    }

    /**
     * Get checkedAt
     *
     * @return \DateTime
     */
    public function getCheckedAt() {
        return $this->checkedAt;
    }

    /**
     * Set useForumAlias
     *
     * @param boolean $useForumAlias
     * @return Replies
     */
    function setUseForumAlias($useForumAlias) {
        $this->useForumAlias = $useForumAlias;
    }

    /**
     * Get useForumAlias
     *
     * @return boolean
     */
    function getUseForumAlias() {
        return $this->useForumAlias;
    }

    /**
     * Set news
     *
     * @param \Theaterjobs\NewsBundle\Entity\News $news
     * @return Replies
     */
    public function setNews(\Theaterjobs\NewsBundle\Entity\News $news = null) {
        $this->news = $news;

        return $this;
    }

    /**
     * Get news
     *
     * @return \Theaterjobs\NewsBundle\Entity\News
     */
    public function getNews() {
        return $this->news;
    }

    /**
     * Set checkedBy
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $checkedBy
     * @return Replies
     */
    public function setCheckedBy(\Theaterjobs\ProfileBundle\Entity\Profile $checkedBy = null) {
        $this->checkedBy = $checkedBy;

        return $this;
    }

    /**
     * Get checkedBy
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getCheckedBy() {
        return $this->checkedBy;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     * @return Replies
     */
    public function setProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile = null) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $archivedAt
     * @return Replies
     */
    public function setArchivedAt($archivedAt) {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getArchivedAt() {
        return $this->archivedAt;
    }

}
