<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Entity for the Education.
 *
 * @ORM\Table(name="tj_inserate_educations")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\EducationRepository"
 * )
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
class Education extends Inserate {

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'educations';

    /**
     * @return unknown
     */
    public function getSubdir()
    {
        return $this->subdir;
    }

    /**
     * @param unknown $subdir
     * @return Education
     */
    public function setSubdir($subdir)
    {
        $this->subdir = $subdir;
        return $this;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column( name="contact", type="string", length=1024, nullable=true)
     */
    protected $contact;
    
     /**
     * @ORM\ManyToOne(targetEntity="EducationKind", inversedBy="education")
     * @ORM\JoinColumn(name="education_kind_id", referencedColumnName="id")
     **/
    protected $educationKind;

    /**
     * (non-PHPdoc)
     * @see LogoPossessor::getType()
     *
     * @return type of the LogoPossessor
     */
    public function getType() {
        return 'tj_inserate_educations';
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
     * Set contact
     *
     * @param string $contact
     * @return Job
     */
    public function setContact($contact) {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact() {
        return $this->contact;
    }

    function getEducationKind() {
        return $this->educationKind;
    }

    function setEducationKind($educationKind) {
        $this->educationKind = $educationKind;
    }


}
