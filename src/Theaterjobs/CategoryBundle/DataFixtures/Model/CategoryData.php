<?php

namespace Theaterjobs\CategoryBundle\DataFixtures\Model;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\CategoryBundle\Entity\Category;

/**
 * Model for Datafixtures of categories
 *
 * @category DataFixtures
 * @package  Theaterjobs\CategoryBundle\DataFixtures\Model
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
abstract class CategoryData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     *
     */
    public function load(ObjectManager $manager) {
        $translatable = $this->container->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale('en');
        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $root = new Category();
        $root->setTitle($this->getRootName());
        $repository->translate($root, 'title', 'de', $this->getRootNameDE());
        $repository->translate($root, 'title', 'sq', $this->getRootNameDE());
        $manager->persist($root);
        $this->setReference("{$this->getRefName()}_{$this->getRootName()}", $root);
        $categories = $this->getCategoryArray();
        foreach ($categories as $name => $children) {
            $main = new Category();
            $main->setTitle($name);
            $main->setParent($root);
            $repository->translate($main, 'title', 'de', $name);
            $repository->translate($main, 'title', 'sq', $name);
            $manager->persist($main);
            $this->setReference("{$this->getRefName()}_{$this->getRootName()}_{$name}", $main);
            if($children){
            foreach ($children as $childname) {
                $child = new Category();
                $child->setTitle($childname);
                $child->setParent($main);
                $repository->translate($child, 'title', 'de', $childname);
                $repository->translate($child, 'title', 'sq', $childname);
                $manager->persist($child);
                $this->setReference("{$this->getRefName()}_{$this->getRootName()}_{$name}_{$childname}", $child);
                $refStr = "{$this->getRefName()}_{$this->getRootName()}_{$name}_{$childname}";
                $this->setReference($refStr, $child);
            }
            }
        }

        $manager->flush();
    }

    /**
     * @return number
     */
    abstract function getOrder();

    /**
     * @return multitype:multitype:string
     */
    abstract function getCategoryArray();

    abstract function getRootName();

    abstract function getRootNameDE();

    abstract function getRefName();
}
