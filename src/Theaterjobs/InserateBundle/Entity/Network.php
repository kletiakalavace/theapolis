<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Entity for the Network.
 *
 * @ORM\Table(name="tj_inserate_networks")
 * @ORM\Entity(
 *    repositoryClass="Theaterjobs\InserateBundle\Entity\NetworkRepository"
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
class Network extends Inserate {

    /**
     * The Discriminator-Map is defined in the parent class.
     * @var unknown
     */
    protected $subdir = 'networks';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * (non-PHPdoc)
     * @see LogoPossessor::getType()
     *
     * @return type of the LogoPossessor
     */
    public function getType() {
        return 'tj_inserate_networks';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

}
