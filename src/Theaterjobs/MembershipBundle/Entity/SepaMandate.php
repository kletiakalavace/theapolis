<?php

namespace Theaterjobs\MembershipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Theaterjobs\MembershipBundle\Model\ProfileInterface;
use Theaterjobs\ProfileBundle\Model\SepaMandateInterface;

/**
 * Entity for the debit account.
 * @ORM\Table(name="tj_membership_sepa_mandates")
 * @ORM\Entity(repositoryClass="Theaterjobs\MembershipBundle\Entity\SepaMandateRepository")
 * @category Entity
 * @package  Theaterjobs\MembershipBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class SepaMandate implements SepaMandateInterface {

    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Theaterjobs\MembershipBundle\Model\ProfileInterface", inversedBy="sepaMandates")
     * @ORM\JoinColumn(name="tj_profile_profiles_id", referencedColumnName="id", nullable=false)
     */
    private $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="mandate_reference", type="string", length=35)
     * @Assert\NotBlank
     */
    private $mandateReference;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=15)
     * @Assert\NotBlank
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/sepaMandates';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set mandateReference
     *
     * @param string $mandateReference
     *
     * @return SepaMandate
     */
    public function setMandateReference($mandateReference) {
        $this->mandateReference = $mandateReference;

        return $this;
    }

    /**
     * Get mandateReference
     *
     * @return string
     */
    public function getMandateReference() {
        return $this->mandateReference;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return SepaMandate
     */
    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Billing
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set profile
     *
     * @param \Theaterjobs\ProfileBundle\Entity\Profile $profile
     *
     * @return SepaMandate
     */
    public function setProfile(ProfileInterface $profile) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    public function getProfile() {
        return $this->profile;
    }

}
