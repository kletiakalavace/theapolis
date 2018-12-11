<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of GeneralSection
 *
 * @ORM\Table(name="tj_profile_section_contact")
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
     * @ORM\OneToOne( targetEntity="Profile", mappedBy="contactSection" )
     */
    protected $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="text", length=1024, nullable=true)
     */
    private $contact;

    /**
     * @ORM\Column(name="geolocation",type="string", length=255 ,nullable=true)
     */
    protected $geolocation;

    /**
     * @ORM\OneToMany( targetEntity="Theaterjobs\ProfileBundle\Entity\ProfileSocialMedia" , mappedBy="contactSection", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     * @Assert\Valid()
     */
    private $social;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", length=1024, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="text", length=1024, nullable=true)
     */
    private $city;

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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return ContactSection
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return ContactSection
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     * @return CvSection
     */
    public function setProfile(\Theaterjobs\ProfileBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
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
     * Set geolocation
     *
     * @param string $geolocation
     *
     * @return Profile
     */
    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * Get geolocation
     *
     * @return string
     */
    public function getGeolocation()
    {
        return $this->geolocation;
    }

    /**
     * @return mixed
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Add director
     *
     * @param ProfileSocialMedia $social
     *
     * @return Profile
     */
    public function addSocial(ProfileSocialMedia $social)
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
     * @param ProfileSocialMedia $social
     */
    public function removeSocial(ProfileSocialMedia $social)
    {
        $this->social->removeElement($social);
    }
}
