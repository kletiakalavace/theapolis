<?php

namespace Theaterjobs\StatsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Theaterjobs\StatsBundle\Model\UserInterface;

/**
 * Entity for holding information about view statistics.
 *
 * @ORM\Entity
 * @ORM\Table(name="tj_stats_views")
 * @ORM\Entity(repositoryClass="Theaterjobs\StatsBundle\Entity\ViewRepository" )
 */
class View {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\StatsBundle\Model\UserInterface", fetch="EAGER")
     * @ORM\JoinColumn(name="tj_stats_users_id", nullable=true)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="object_class", type="string", length=255, nullable=true)
     */
    protected $objectClass;

    /**
     * @ORM\Column(name="foreign_key", type="integer")
     */
    protected $foreignKey;

    /**
     * @var string
     * @ORM\Column(name="ip", type="string")
     */
    protected $ip;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set objectClass
     *
     * @param string $objectClass
     * @return View
     */
    public function setObjectClass($objectClass) {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * Get objectClass
     *
     * @return string
     */
    public function getObjectClass() {
        return $this->objectClass;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return View
     */
    public function setIp($ip) {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return View
     */
    public function setUser(UserInterface $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser() {
        return $this->user;
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
     * Set createdAt
     *
     * @param \DateTime createdAt
     * @return View
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set foreignKey
     *
     * @param integer $foreignKey
     * @return View
     */
    public function setForeignKey($foreignKey) {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey
     *
     * @return integer
     */
    public function getForeignKey() {
        return $this->foreignKey;
    }

}
