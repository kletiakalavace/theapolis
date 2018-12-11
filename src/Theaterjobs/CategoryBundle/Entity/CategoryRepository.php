<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Theaterjobs\CategoryBundle\Entity;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityRepository;
use Theaterjobs\CategoryBundle\Entity\Category;
use Theaterjobs\CategoryBundle\Exception\UnexpectedRootTitleException;

/**
 * Description of CategoryRepository
 *
 * @category Entity
 * @package  Theaterjobs\CategoryBundle\Entity
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class CategoryRepository extends NestedTreeRepository
{

    /**
     * Finds ChoiceList by title.
     *
     * @param String $rootSlug
     * @param bool $parent
     * @param bool $title
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findChoiceListBySlug($rootSlug, $parent = false, $title = false)
    {
        $qb = $this->getRootNodesQueryBuilder();
        $qb->where($qb->expr()->eq('node.slug', ':root_slug'))->setParameter('root_slug', $rootSlug);
        $node = null;
        try {
            $node = $qb->getQuery()->getOneOrNullResult();
        } catch (NoResultException $e) {

            $msg = $e->getMessage();
            $msg .= "\nCould not find a root with root: " . $rootSlug;
            throw new UnexpectedRootTitleException($msg);
        }
        $query = $this->getNodesHierarchyQuery($node);
        $query->useQueryCache(false);
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $full = $query->getDQL();


        $before = substr($full, 0, (strpos($full, "ORDER")));
        $after = substr($full, (strpos($full, "ORDER")));
        if (strpos(strtolower($full), 'where') !== false)
            $full = $before . " AND node.removedAt IS NULL " . $after;
        else
            $full = $before . " WHERE node.removedAt IS NULL " . $after;
        $query->setDQL($full);
        //getNodesHierarchy
        $nodes = $query->getResult();
        $choices = $this->generateChoicesArray($nodes, $parent, $title);

        return $choices;
    }

    /**
     * @param array $slugs
     * @return array
     */
    public function getTitlesBySlugs(array $slugs)
    {
        if (!$slugs) {
            return [];
        }
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.title,c.slug')
            ->where($qb->expr()->in('c.slug', $slugs));

        return $qb->getQuery()->getResult();
    }

    /**
     * Builds Array for choices
     *
     * @param array $result
     * @param $parent
     * @param bool $title
     * @return array
     */
    private function generateChoicesArray($result, $parent, $title = false)
    {

        $choices = [];
        $choicesAll = [];
        $choicesSub = [];
        $curr = null;
        $catArray = ['new-categories-of-markets', "neue-Marktkategorien"];

        if ($result[0]->getParent()->getParent() && in_array($result[0]->getParent()->getParent()->getSlug(), $catArray)) {
            foreach ($result as $cat) {
                $choices[] = $cat;
            }
        } else {
            foreach ($result as $cat) {
                if ($cat->getParent()->getParent() === null) {
                    $curr = $cat;
                } else {
                    if ($parent == $curr->getSlug()) {
                        $choicesSub[$cat->getId()] = $title ? $cat->getTitle() : $cat->getSlug();
                    } else {
                        $choicesAll[$curr->getSlug()][] = $cat;
                    }
                }
            }
        }
        return $parent ? $choicesSub : $choicesAll;
    }
}