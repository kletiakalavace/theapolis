<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Occupation
 *
 * @ORM\Table(name="tj_profile_production_occupation")
 * @ORM\Entity(repositoryClass="Theaterjobs\ProfileBundle\Entity\OccupationRepository")
 */
class Occupation
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
     * @ORM\OneToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\ProductionParticipations", mappedBy="occupationDescription")
     */
    protected $production;

    /**
     * @var integer
     *
     * @ORM\Column(name="role_id", type="integer", nullable=true)
     */
    private $roleId = null;

    /**
     * @var string
     *
     * @ORM\Column(name="role_name", type="string", length=100, nullable=true)
     */
    private $roleName = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="assistant", type="boolean", nullable=true)
     */
    private $assistant = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="management", type="boolean", nullable=true)
     */
    private $management =false;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text",  nullable=true)
     */
    private $description;


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
     * Set production
     *
     * @param \Theaterjobs\ProfileBundle\Entity\ProductionParticipations $production
     *
     * @return Occupation
     */
    public function setProduction($production)
    {
        $this->production = $production;

        return $this;
    }

    /**
     * Get production
     *
     * @return integer
     */
    public function getProduction()
    {
        return $this->production;
    }

    /**
     * Set roleId
     *
     * @param integer $roleId
     *
     * @return Occupation
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId
     *
     * @return integer
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set roleName
     *
     * @param integer $roleName
     *
     * @return Occupation
     */

    public function setroleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get roleName
     *
     * @return integer
     */

    public function getroleName()
    {
        return $this->roleName;
    }

    /**
     * Set assistant
     *
     * @param boolean $assistant
     *
     * @return Occupation
     */
    public function setAssistant($assistant)
    {
        $this->assistant = $assistant;

        return $this;
    }

    /**
     * Get assistant
     *
     * @return boolean
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * @return bool
     */
    public function isManagement()
    {
        return $this->management;
    }

    /**
     * @param bool $management
     */
    public function setManagement($management)
    {
        $this->management = $management;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Occupation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}

