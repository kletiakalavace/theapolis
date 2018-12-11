<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of GeneralSection
 *
 * @ORM\Table(name="tj_inserate_section_contact")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ContactSection
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne( targetEntity="Theaterjobs\InserateBundle\Entity\Organization", mappedBy="contactSection" )
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="text", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="text", length=1024, nullable=true)
     */
    private $contact;

    /**
     * @ORM\OneToMany( targetEntity="Theaterjobs\InserateBundle\Entity\OrganizationSocialMedia" , mappedBy="contactSection", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     * @Assert\Valid()
     */
    private $social;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->social = new ArrayCollection();
    }

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
     * Set organization
     *
     * @param \Theaterjobs\InserateBundle\Entity\Organization $organization
     * @return ContactSection
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return ContactSection
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return mixed
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Add contact section
     *
     * @param OrganizationSocialMedia $social
     *
     * @return ContactSection
     */
    public function addSocial(OrganizationSocialMedia $social)
    {
        $social->setContactSection($this);
        $this->social[] = $social;

        return $this;
    }

    public function setSocial(array $items)
    {
        if (!empty($items) && $items === $this->social) {
            reset($items);
            $items[key($items)] = clone current($items);
        }
        $this->social = $items;
    }

    /**
     * Remove director
     *
     * @param OrganizationSocialMedia $social
     */
    public function removeSocial(OrganizationSocialMedia $social)
    {
        $this->social->removeElement($social);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

}
