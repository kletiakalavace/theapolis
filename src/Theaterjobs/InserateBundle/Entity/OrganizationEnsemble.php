<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrganizationEnsemble
 *
 * @ORM\Table(name="tj_inserate_organizations_ensemble")
 * @ORM\Entity(repositoryClass="Theaterjobs\InserateBundle\Entity\OrganizationEnsembleRepository")
 */
class OrganizationEnsemble
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
     * @ORM\ManyToMany(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="organizationEnsemble", cascade={"persist"})
     * @ORM\JoinTable(name="tj_organization_ensemble_users")
     * */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationEnsemble", cascade={"persist"})
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * 
     */
    private $organization;


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
     *
     * @return OrganizationEnsemble
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
     * Get users
     *
     * @return integer
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set organization
     *
     * @param integer $organization
     *
     * @return OrganizationEnsemble
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return integer
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $user
     *
     * @return OrganizationEnsemble
     */
    public function addUser(\Theaterjobs\ProfileBundle\Entity\Profile $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $user
     */
    public function removeUser(\Theaterjobs\ProfileBundle\Entity\Profile $user)
    {
        $this->users->removeElement($user);
    }
}
