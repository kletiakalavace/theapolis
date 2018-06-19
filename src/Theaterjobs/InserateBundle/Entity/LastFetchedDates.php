<?php
namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Vacancy
 *
 * @ORM\Table("tj_inserate_last_dates")
 * @ORM\Entity
 */
class LastFetchedDates {
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
     * @ORM\Column(name="website", type="string", length=255)
     */
    protected $website;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastDate", type="datetime", nullable=true)
     */
    protected $lastDate;

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
     * Set website
     *
     * @param string $website
     *
     * @return LastFetchedDates
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set archivedAt
     *
     * @param \DateTime $lastDate
     *
     * @return LastFetchedDates
     */
    public function setLastDate($lastDate)
    {
        $this->lastDate = $lastDate;

        return $this;
    }

    /**
     * Get archivedAt
     *
     * @return \DateTime
     */
    public function getLastDate()
    {
        return $this->lastDate;
    }
}
