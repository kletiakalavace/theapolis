<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of GeneralSection
 *
 * @ORM\Table(name="tj_profile_section_biography")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class BiographySection  {
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
        
    /**
     * @ORM\OneToOne( targetEntity="Profile", mappedBy="biographySection" )
     */
    protected $profile;
    
    /**
     * @var string
     *
     * @ORM\Column(name="biography", type="text" , length=4096, nullable=true)
     */
    private $biography;

    function getId() {
        return $this->id;
    }

    function getBiography() {
        return $this->biography;
    }

    function setBiography($biography) {
        $this->biography = $biography;
    }

    function getProfile() {
        return $this->profile;
    }

    function setProfile($profile) {
        $this->profile = $profile;
    }

}
