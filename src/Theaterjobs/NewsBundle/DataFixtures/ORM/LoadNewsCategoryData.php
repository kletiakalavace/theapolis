<?php

namespace Theaterjobs\NewsBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\CategoryBundle\Entity\Category;
use Carbon\Carbon;

/**
 * Description of LoadNewsCategoryData
 *
 * @author malvin
 */
class LoadNewsCategoryData extends CategoryData {

    protected $rootname = "categories of news";
    protected $rootnameDE = "Newskategorien";
    protected $refname = "newscategory";

    /**
     * Load the fixtures
     *
     * @param ObjectManager $manager
     */
    public function getCategoryArray() {

        $categories = array(
            'Häuser' => array(),
            'in eigener Sache' => array(),
            'Kulturpolitik' => array(),
            'Leitungswechsel' => array('removedAt' => true),
            'Menschen' => array(),
            'Preise' => array(),
            'Presseschau' => array(),
            'Termine' => array('removedAt' => true),
            'Vermischtes' => array(),
            'Rechtliches' => array(),
            'Meinung' => array(),
        );


        return $categories;
    }

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
        foreach ($categories as $name => $attribute) {
            $main = new Category();
            $main->setTitle($name);
            $main->setParent($root);
            $repository->translate($main, 'title', 'de', $name);
            $repository->translate($main, 'title', 'sq', $name);
            $manager->persist($main);
            $this->setReference("{$this->getRefName()}_{$this->getRootName()}_{$name}", $main);
            if ($attribute) {
                if ($attribute['removedAt'] == true) {
                    $dateRemove = (new Carbon())->addWeeks(2);
                    $main->setRemovedAt($dateRemove);
                }
            }
        }

        $manager->flush();
    }

    public function getRefName() {
        return $this->refname;
    }

    public function getRootName() {
        return $this->rootname;
    }

    public function getRootNameDE() {
        return $this->rootnameDE;
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 1;
    }

}
