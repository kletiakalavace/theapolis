<?php
/**
 * User: malvin
 * Date: 6/25/16
 * Time: 12:50 AM
 */

namespace Theaterjobs\ProfileBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Theaterjobs\AdminBundle\Model\SkillSearch;

class SkillRepository extends NestedTreeRepository
{
    public function getCheckedSkills()
    {
        $qb = $this->createQueryBuilder('skill');
        $qb->where($qb->expr()->eq('skill.checked', ':param'));
        $qb->setParameter(':param', true);
        $qb->orderBy('skill.title', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getAllLanguages()
    {
        $qb = $this->createQueryBuilder('skill');
        $qb->where($qb->expr()->eq('skill.checked', ':param'));
        $qb->where('skill.isLanguage = 1 ');
        $qb->orderBy('skill.title', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getAllSkills()
    {
        $qb = $this->createQueryBuilder('skill');
        $qb->where($qb->expr()->eq('skill.checked', ':param'));
        $qb->where('skill.isLanguage = 0 ');
        $qb->orderBy('skill.title', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getUnCheckedSkills()
    {
        $qb = $this->createQueryBuilder('skill');
        $qb->where($qb->expr()->eq('skill.checked', ':param'));
        $qb->setParameter(':param', false);
        $qb->orderBy('skill.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function mergeSkillQueryBuilder(Skill $skill)
    {
        //@Todo ask Soeren which skills will be available for merge,displaying all skills instead
        $qb = $this->createQueryBuilder('skill');
        //->where('skill.checked = TRUE');
        $qb->andWhere($qb->expr()->neq('skill.id', $skill->getId()))
            ->orderBy('skill.title', 'ASC');

        return $qb;
    }

    public function languageToAutosuggestion($title)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $languageSkill = $qb->select('s.title')
            ->from("TheaterjobsProfileBundle:LanguageSkill", 'ls')
            ->join('ls.skill', 's')
            ->where('s.title LIKE ?1 ')
            ->andWhere($qb->expr()->eq('s.checked', ':param'))
            ->setParameter('1', '%' . $title . '%')
            ->setParameter(':param', true);

        return $qb->getQuery()->getResult();
    }

    public function getlanguageSkill($title)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $languageSkill = $qb->select('s.title')
            ->from("TheaterjobsProfileBundle:Skill", 's')
            ->where('s.title LIKE ?1 ')
            ->andWhere($qb->expr()->eq('s.checked', ':param'))
            ->andWhere($qb->expr()->eq('s.isLanguage', ':param1'))
            ->setParameter('1', '%' . $title . '%')
            ->setParameter(':param', true)
            ->setParameter(':param1', true);

        return $qb->getQuery()->getResult();
    }

    public function skillToAutosuggestion($title)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
        $otherSkill = $qb->select('s.title')
            ->from("TheaterjobsProfileBundle:SkillSection", 'ss')
            ->join('ss.profileSkill', 's')
            ->where('s.title LIKE :title ')
            ->andWhere($qb->expr()->eq('s.checked', ':checked'))
            ->andWhere($qb->expr()->eq('s.isLanguage', ':language'))
            ->setParameter('title', '%' . $title . '%')
            ->setParameter('checked', true);
        return $qb->getQuery()->getResult();
    }

    public function getOtherSkill($title, $language)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->addSelect("(CASE WHEN s.title like  '" . $title . "%'   THEN 1  WHEN s.title like '%" . $title . "'  THEN 2 ELSE 3 END) AS HIDDEN ordCol")
            ->where('s.title LIKE :title ')
            ->andWhere($qb->expr()->eq('s.checked', ':checked'))
            ->andWhere($qb->expr()->eq('s.isLanguage', ':language'))
            ->setParameter('title', '%' . $title . '%')
            ->setParameter('checked', true)
            ->setParameter('language', $language)
            ->orderBy("ordCol");

        return $qb->getQuery();
    }

    public function getSiblingsByRoot($root)
    {
        $qb = $this->createQueryBuilder('node');
        $qb->orderBy('node.root, node.lft', 'ASC')
            ->where($qb->expr()->eq('node.root', ':root'))
            ->setParameter('root', $root)
            ->getQuery();

        return $qb->getQuery()->getResult();
    }


    /**
     * @param SkillSearch $formSearch
     * @return mixed
     */
    public function adminListSearch(SkillSearch $formSearch)
    {

        $qb = $this->createQueryBuilder('s');

        $qb
            ->where("s.isLanguage = :language")
            ->setParameter("language", (boolean)$formSearch->isLanguage());

        if ($formSearch->getTitle()) {
            $qb
                ->andWhere($qb->expr()->like('s.title', ':title'))
                ->setParameter('title', sprintf('%%%s%%', $formSearch->getTitle()));
        }

        if (is_numeric($formSearch->getChoices())) {
            $qb
                ->andWhere($qb->expr()->eq('s.checked', ':checked'))
                ->setParameter('checked', $formSearch->getChoices());
        }

        if ($formSearch->getOrderCol()) {
            $qb->orderBy(sprintf("s.%s", $formSearch->getOrderCol()), $formSearch->getOrder());
        }


        return $qb->getQuery();
    }

}