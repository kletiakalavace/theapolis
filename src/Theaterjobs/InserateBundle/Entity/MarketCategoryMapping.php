<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the inserate.
 *
 * @ORM\Table(name="tj_inserate_marketcategory_mapping")
 * @ORM\Entity
 *
 * @category Entity
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 */
class MarketCategoryMapping {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface")
     * @ORM\JoinColumn(name="tj_inserate_old_category_id", referencedColumnName="id")
     */
    protected $oldMarket;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\InserateBundle\Model\CategoryInterface")
     * @ORM\JoinColumn(name="tj_inserate_new_category_id", referencedColumnName="id")
     */
    protected $newMarket;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set oldMarket
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $oldMarket
     * @return MarketCategoryMapping
     */
    public function setOldMarket(\Theaterjobs\CategoryBundle\Entity\Category $oldMarket = null) {
        $this->oldMarket = $oldMarket;

        return $this;
    }

    /**
     * Get oldMarket
     *
     * @return \Theaterjobs\CategoryBundle\Entity\Category
     */
    public function getOldMarket() {
        return $this->oldMarket;
    }

    /**
     * Set newMarket
     *
     * @param \Theaterjobs\CategoryBundle\Entity\Category $newMarket
     * @return MarketCategoryMapping
     */
    public function setNewMarket(\Theaterjobs\CategoryBundle\Entity\Category $newMarket = null) {
        $this->newMarket = $newMarket;

        return $this;
    }

    /**
     * Get newMarket
     *
     * @return \Theaterjobs\CategoryBundle\Entity\Category
     */
    public function getNewMarket() {
        return $this->newMarket;
    }

}
