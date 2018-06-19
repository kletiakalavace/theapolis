<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Theaterjobs\CategoryBundle\DataFixtures\Model\CategoryData;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\CategoryBundle\Entity\Category;
use Carbon\Carbon;
/**
 * Datafixtures for the Profilecategories.
 *
 * @category DataFixtures
 * @package  Theaterjobs\ProfileBundle\DataFixtures\ORM
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class LoadProfileCategoryData extends CategoryData {

    protected $rootname = "categories of profiles";
    protected $rootnameDE = "Profilkategorien";
    protected $refname = "profilecategory";

    /**
     * @return number
     */
    public function getOrder() {
        return 30;
    }

    /**
     * @return multitype:multitype:string
     */
    public function getCategoryArray() {
        /**
         * Schauspieler (männliche Rollen), Schauspieler (weibliche Rollen), sonstige Schauspiel,
         * Tänzer (männliche Rollen), Tänzer (weibliche Rollen), sonstige Tanz,
         * Sänger (männliche Solopartien), Sänger (weibliche Solopartien), sonstige Musiktheater,
         * Sopran, Mezzo, Alt, Tenor,  Bariton, Bass, sonstige Chor,
         * Statisterie, Performance, Moderation/Sprechen,
         * Entertainment, Artistik/Kleinkunst, Puppenspiel/Figurentheater,
         */
        $categories = array(
            'Schauspiel' => array(
                array('Autoren',false),
                array('Schauspieler (männliche Rollen)',true),
                array('Schauspieler (weibliche Rollen)',true),
                array('Schauspielregie',false),
                array('Schauspielmusik',false),
                array('sonstige Schauspiel',true)
            ),
            'Tanz' => array(
                array('Choreografie / Direktion',false),
                array('Tänzer (männliche Rollen)',true),
                array('Tänzer (weibliche Rollen)',true),
                array('sonstige Tanz',true)
            ),
            'Musiktheater' => array(
                array('Komposition',false),
                array('Dirigat, Musikalische Leitung',false),
                array('Korrepetition',false),
                array('Musiktheaterregie',false),
                array('Sänger (männliche Solopartien)',true),
                array('Sänger (weibliche Solopartien)',true),
                array('sonstige Musiktheater',true)
            ),
            'Chor' => array(
                array('Chorleitung',false),
                array('Sopran',true),
                array('Mezzo',true),
                array('Alt',true),
                array('Tenor',true),
                array('Bariton',true),
                array('Bass',true),
                array('sonstige Chor',true),
            ),
            'Orchester' => array(
                array('Streichinstrumente',false),
                array('Blechblasinstrumente',false),
                array('Holzblasinstrumente',false),
                array('sonstige Orchester',false)
            ),
            'Ausstattung' => array(
                array('Ausstattung',false),
                array('Bühnenbild',false),
                array('Bühnenmalerei und -plastik',false),
                array('Dekoration',false),
                array('Requisite',false),
                array('Schlosserei',false),
                array('Tischlerei',false),
                array('Kostümbild',false),
                array('Ankleidedienst',false),
                array('Gewandmeisterei',false),
                array('Schneiderei',false),
                array('Maske',false),
                array('Lichtdesign',false),
                array('Sounddesign',false),
                array('sonstige Ausstattung',false)
            ),
            'Technik' => array(
                array('Inspizienz',false),
                array('Beleuchtungstechnik',false),
                array('Ton- und Videotechnik',false),
                array('Technische Leitung',false),
                array('Techn. Produktionsleitung',false),
                array('Werkstättenleitung',false),
                array('Bühneninspektion',false),
                array('Bühnen- und Veranstaltungstechnik',false),
                array('Haustechnik',false),
                array('sonstige Technik',false)
            ),
            'Organisation' => array(
                array('Dramaturgie',false),
                array('Intendanz',false),
                array('Intendanzsekretariat',false),
                array('Soufflage',false),
                array('Theaterpädagogik',false),
                array('Disposition (KBB)',false),
                array('Presse- und Öffentlichkeitsarbeit',false),
                array('Produktionsleitung / Company Management',false),
                array('Web / Grafikdesign',false),
                array('Akquise / Booking',false),
                array('Marketing / Werbung',false),
                array('Sponsoring / Fundraising',false),
                array('sonstige Organisation',false)
            ),
            'Administration' => array(
                array('Verwaltungsleitung / kaufm. GF',false),
                array('Personalwesen',false),
                array('Kasse',false),
                array('Finanz- und Rechnungswesen',false),
                array('Vertrieb',false),
                array('Archiv / Bibliothek',false),
                array('Hausverwaltung',false),
                array('IT / EDV',false),
                array('Service / Vorderhaus',false),
                array('sonstige Administration',false)
            ),
            'Sonstige' => array(
                array('Performance',false),
                array('Moderation / Sprechen',false),
                array('Entertainment',false),
                array('Artistik / Kleinkunst',false),
                array('Puppenspiel / Figurentheater',false)
            )
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
//                var_dump($childname);
//                exit();
                $child = new Category();
                $child->setTitle($childname[0]);
                $child->setIsPerformanceCategory($childname[1]);
                if(isset($childname[2]) && $childname[2]==true){
                    $date = new Carbon();
                    $dateRemove = $date->addWeeks(2);
                    $child->setRemovedAt($dateRemove);
                }
                $child->setParent($main);
                $repository->translate($child, 'title', 'de', $childname[0]);
                $repository->translate($child, 'title', 'sq', $childname[0]);
                $manager->persist($child);
                $this->setReference("{$this->getRefName()}_{$this->getRootName()}_{$name}_{$childname[0]}", $child);
                $refStr = "{$this->getRefName()}_{$this->getRootName()}_{$name}_{$childname[0]}";
                $this->setReference($refStr, $child);
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

}
