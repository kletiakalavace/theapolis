<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the gratification.
 *
 * @ORM\Table(name="tj_inserate_video_links")
 * @ORM\Entity()
 */
class EmbededVideos {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Inserate", inversedBy="videos")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *
     */
    protected $inserate;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=128)
     */
    protected $url;
    
    public function getId() {
        return $this->id;
    }

    public function getType() {
        return 'tj_inserate_videos';
    }

    function getInserate() {
        return $this->inserate;
    }

    function setInserate($inserate) {
        $this->inserate = $inserate;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return EmbededVideos
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
