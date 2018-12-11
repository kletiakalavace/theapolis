<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * OrganizationSocialMedia
 *
 * @ORM\Table(name="tj_organization_social_media", uniqueConstraints={@UniqueConstraint(name="tj_contact_social_media_unique", columns={"contact_section_id", "social_media_id"})})
 * @UniqueEntity(fields={"contactSection","socialMedia"}, errorPath="socialMedia", message="Duplicated Social media for this profile!")
 * @ORM\Entity
 */
class OrganizationSocialMedia
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
     * @ORM\Column(name="link", type="string", length=255)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Entity\ContactSection", inversedBy="social")
     * @ORM\JoinColumn(name="contact_section_id", referencedColumnName="id")
     **/
    private $contactSection;

    /**
     * @return mixed
     */
    public function getContactSection()
    {
        return $this->contactSection;
    }

    /**
     * @param mixed $contactSection
     * @return OrganizationSocialMedia
     */
    public function setContactSection($contactSection)
    {
        $this->contactSection = $contactSection;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\AdminBundle\Entity\SocialMedia", inversedBy="organizationMedia")
     * @ORM\JoinColumn(name="social_media_id", referencedColumnName="id")
     * @OrderBy({"position" = "ASC"})
     **/
    private $socialMedia;

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
     * Set link
     *
     * @param string $link
     *
     * @return OrganizationSocialMedia
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return mixed
     */
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * @param mixed $socialMedia
     * @return OrganizationSocialMedia
     */
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;
        return $this;
    }
}

