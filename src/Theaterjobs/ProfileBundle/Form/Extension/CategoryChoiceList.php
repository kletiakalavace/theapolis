<?php

namespace Theaterjobs\ProfileBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Doctrine\ORM\EntityManager;

/**
 * Creates a category choice list.
 *
 * @category Extension
 * @package  Theaterjobs\ProfileBundle\Extension\ChoiceList
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class CategoryChoiceList extends LazyChoiceList
{

    protected $em;
    protected $roots;
    protected $rootKey;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em The Entity Manager.
     */
    public function __construct(EntityManager $em, array $roots, $rootKey)
    {
        $this->em = $em;
        $this->roots = $roots;
        $this->rootKey = $rootKey;
    }

    /* (non-PHPdoc)
     * @see \Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList::loadChoiceList()
     */

    protected function loadChoiceList()
    {
        $qb = $this->em->createQueryBuilder();
//        $qb->select('c, s')->from("TheaterjobsProfileBundle:Category", "c");
//        $qb->leftJoin('c.children', 's');
//        $qb->where($qb->expr()->andX(
//                $qb->expr()->eq('c.root', ':root'), $qb->expr()->eq('c.lvl', ':lvl')
//        ));
//        $qb->orderBy('c.id')->addOrderBy('s.id');
//
//        $qb->setParameters(
//            array(
//                'root' => $this->roots[$this->rootKey],
//                'lvl' => 1
//            )
//        );
//
//        $query = $qb->getQuery();
//
//        $query->setHint(
//            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
//        );
//
//        $choices = array();
//        $current = null;
//        $result = $query->getResult();
//        foreach ($result as $cat) {
//            if ($current === null || $current !== $cat->getName()) {
//                $current = $cat->getName();
//            }
//
//            $choices[$cat->getName()] = array();
//
//            foreach ($cat->getChildren() as $sub) {
//                $choices[$cat->getName()][] = $sub;
//            }
//        }
        $qb = $this->createQueryBuilder('c')->from("TheaterjobsCategoryBundle:Category", "c");
        $result = $qb->select('c.id')
            ->innerJoin('c.parent', 'parent')
            ->innerJoin('parent.parent', 'root')
            ->where('root.slug = :rootName')
            ->andWhere('c.isPerformanceCategory =:param')
            ->andWhere('c.removedAt is NULL')
            ->setParameters(array('rootName' => 'categories-of-profiles', 'param' => true))
            ->getQuery()->getArrayResult();
        // $categories = array_column($result, "id");

        return new ObjectChoiceList($result, null, array(), null, 'id');
    }

}
