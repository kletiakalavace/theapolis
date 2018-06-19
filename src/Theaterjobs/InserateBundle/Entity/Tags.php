<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Tags
 *
 * @ORM\Table("tj_tags_organization")
 * * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\TagsRepository"
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="checked", type="boolean", nullable=false)
     */
    protected $checked = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checked_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $checkedAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\ProfileInterface")
     * @ORM\JoinColumn(name="checked_by", referencedColumnName="id")
     */
    private $checkedBy;

    /**
     * @ORM\ManyToMany(targetEntity="Theaterjobs\InserateBundle\Entity\OrganizationStage", mappedBy="tags", cascade={"persist"})
     **/
    private $organizationStage;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

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
    
    function getOrganizationStage() {
        return $this->organizationStage;
    }

    function setOrganizationStage($organizationStage) {
        $this->organizationStage = $organizationStage;
    }

    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * @param mixed $createdAt
     * @return Tags
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Tags
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}
