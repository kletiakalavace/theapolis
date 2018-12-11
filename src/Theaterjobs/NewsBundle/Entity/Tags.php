<?php

namespace Theaterjobs\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Tags
 *
 * @ORM\Table("tj_tags")
 * * @ORM\Entity(
 *    repositoryClass="Theaterjobs\NewsBundle\Entity\TagsRepository"
 * )
 */
class Tags
{
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
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="News", mappedBy="tags", cascade={"persist"})
     **/
    private $news;
    

   // private $forums;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checked_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $checkedAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\NewsBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="checked_by", referencedColumnName="id")
     */
    private $checkedBy;        

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
     * Set title
     *
     * @param string $title
     * @return Tags
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->news = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add news
     *
     * @param \Theaterjobs\NewsBundle\Entity\News $news
     * @return Tags
     */
    public function addNews(\Theaterjobs\NewsBundle\Entity\News $news)
    {
        $this->news[] = $news;
        $news->addTag($this);
        return $this;
    }

    /**
     * Remove news
     *
     * @param \Theaterjobs\NewsBundle\Entity\News $news
     */
    public function removeNews(\Theaterjobs\NewsBundle\Entity\News $news)
    {
        $this->news->removeElement($news);
    }

    /**
     * Get news
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNews()
    {
        return $this->news;
    }

    /**
     * Set checkedAt
     *
     * @param \DateTime $checkedAt
     * @return Tags
     */
    public function setCheckedAt($checkedAt)
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }

    /**
     * Get checkedAt
     *
     * @return \DateTime 
     */
    public function getCheckedAt()
    {
        return $this->checkedAt;
    }

    /**
     * Set checkedBy
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $checkedBy
     * @return Tags
     */
    public function setCheckedBy(\Theaterjobs\ProfileBundle\Entity\Profile $checkedBy = null)
    {
        $this->checkedBy = $checkedBy;

        return $this;
    }

    /**
     * Get checkedBy
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile 
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

}
