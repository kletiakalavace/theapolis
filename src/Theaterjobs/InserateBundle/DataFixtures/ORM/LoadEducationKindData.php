<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Carbon\Carbon;
use Theaterjobs\InserateBundle\Entity\EducationKind;

/**
 * Description of LoadEducationKindData
 *
 * @author abame
 */
class LoadEducationKindData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface {
        /**
     * @var ContainerInterface
     */
    private $container;

    /**
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     *
     * @return number
     */
    public function getOrder() {
        return 120;
    }
    
    public function load(ObjectManager $manager) {        
        $educationKind = array(
            "Ausbildung",
            "Weiterbildung",
            "Hospitanz/Praktium",
            "Stipendium/Akademie",
            "Wettbewerb/Preis",
            "Unterricht",
            "Sonstiges"
        );
        
        foreach ($educationKind as $kind){
            $newEducationKind = new EducationKind();
            $newEducationKind->setName($kind);
            $newEducationKind->setCreatedAt(Carbon::now());
            $manager->persist($newEducationKind);
        }
        
        $manager->flush();
        
    }
}
