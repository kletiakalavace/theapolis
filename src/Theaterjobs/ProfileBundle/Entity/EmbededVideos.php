<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_profile_video_links")
 * @ORM\Entity()
 */
class EmbededVideos
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="videos")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $profile;

    /**
     * @var string
     * @ORM\Column(name="url", type="string", length=128)
     */
    protected $url;

    /**
     * @var integer
     * @ORM\Column(name="position",type="integer",nullable=true)
     */
    protected $position;

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
     * @return EmbededVideos
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

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

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return 'tj_profile_profile_videos';
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

}
