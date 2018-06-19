<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Theaterjobs\ProfileBundle\Entity\TypeOfCategory;

/**
 * Datafixtures for the type of category.
 *
 * @category DataFixtures
 * @package  Theaterjobs\ProfileBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadTypeOfCategoryData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * The container
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * Set the container interface
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * Load the fixtures
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager) {
        foreach ($this->getTypes() as $name => $categories) {
            $type = new TypeOfCategory();
            $type->setName($name);
            foreach ($categories as $maincat => $subcats) {
                $this->setCatAsRef($type, $maincat, $subcats);
            }
            $manager->persist($type);
            $manager->flush();
            $this->setReference("profilecategorytype_$name", $type);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 50;
    }

    private function setCatAsRef(TypeOfCategory $type, $maincat, array $subcats) {
        foreach ($subcats as $subcat) {
            $ref = "profilecategory_categories of profiles_{$maincat}_{$subcat}";
            if ($this->hasReference($ref)) {
                $type->addCategory($this->getReference($ref));
            }
        }
    }

    private function getTypes() {
        $types = array(
            'actor' => array(
                'Schauspiel' => array(
                    'Schauspieler (männliche Rollen)',
                    'Schauspieler (weibliche Rollen)',
                    'sonstige Schauspiel')
            ),
            'dancer' => array(
                'Tanz' => array(
                    'Tänzer (männliche Rollen)',
                    'Tänzer (weibliche Rollen)',
                    'sonstige Tanz'
                )
            ),
            'singer' => array(
                'Musiktheater' => array(
                    'Sänger (männliche Solopartien)',
                    'Sänger (weibliche Solopartien)',
                    'sonstige Musiktheater'
                )
            ),
            'voice' => array(
                'Chor' => array(
                    'Sopran',
                    'Mezzo',
                    'Alt',
                    'Tenor',
                    'Bariton',
                    'Bass',
                    'sonstige Chor'
                )
            ),
            'others' => array(
                'Sonstige' => array(
                    'Statisterie',
                    'Performance',
                    'Moderation / Sprechen',
                    'Entertainment',
                    'Artistik / Kleinkunst',
                    'Puppenspiel / Figurentheater'
                )
            )
        );

        return $types;
    }

}
