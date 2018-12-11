<?php

namespace Theaterjobs\ProfileBundle\Entity;

/**
 * ProductionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductionRepository extends \Doctrine\ORM\EntityRepository
{

    function findAllLikeName($name)
    {
        $qb = $this->createQueryBuilder('p');
        $prod = $qb->select('p')
            ->where($qb->expr()->like('p.name', ':name'))
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();

        return $prod;
    }

    function findOrganization($orgaId)
    {
        $qb = $this->createQueryBuilder('p');
        $prodCat = $qb->select('orga2.name as orga')
            ->innerJoin('p.organizationRelated', "orga2")
            ->innerJoin('orga2.organizationRelated', "o")
            ->where("o.id = :orgaId")
            ->setParameter('orgaId', $orgaId)
            ->getQuery()
            ->getResult();

        return $prodCat;
    }

    /**
     * Production autosuggestion
     *
     * @param string $name
     * @param string $organizationName
     * @return \Doctrine\ORM\Query
     */
    public function tagSuggest($name, $organizationName)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->innerJoin('p.organizationRelated', 'o')
            ->where($qb->expr()->like('p.name', ':name'))
            ->andWhere($qb->expr()->eq('o.name', ':organizationName'))
            ->andWhere($qb->expr()->isNotNull('p.checkedAt'))
            ->andWhere($qb->expr()->isNull('p.archivedAt'))
            ->setParameters([
                'name' => '%' . $name . '%',
                'organizationName' => $organizationName
            ]);

        return $qb->getQuery();
    }

    public function getCheckedProductions()
    {
        $qb = $this->createQueryBuilder("p");
        $qb->where($qb->expr()->isNotNull('p.checkedAt'))
            ->andWhere($qb->expr()->isNull('p.archivedAt'));
        return $qb->getQuery()->getResult();
    }

    public function getUncheckedProductions()
    {
        $qb = $this->createQueryBuilder("p");
        $qb->where($qb->expr()->isNull('p.checkedAt'))
            ->andWhere($qb->expr()->isNull('p.archivedAt'));
        return $qb->getQuery()->getResult();
    }

    public function getArchivedProductions()
    {
        $qb = $this->createQueryBuilder("p");
        $qb->where($qb->expr()->isNull('p.checkedAt'))
            ->andWhere($qb->expr()->isNotNull('p.archivedAt'));
        return $qb->getQuery()->getResult();
    }

    public function checkForCreators($creatorId)
    {
        $qb = $this->createQueryBuilder('prod');
        $qry = $qb->select('count(prod)')
            ->join('prod.creators', 'creators')
            ->where($qb->expr()->eq('creators.id', ':id'))
            ->setParameter('id', $creatorId);
        $prod = $qry->getQuery()->getResult();
        return $prod;
    }

    public function checkForDirectors($directorId)
    {
        $qb = $this->createQueryBuilder('prod');
        $qry = $qb->select('count(prod)')
            ->join('prod.directors', 'directors')
            ->where($qb->expr()->eq('directors.id', ':id'))
            ->setParameter('id', $directorId);
        $prod = $qry->getQuery()->getResult();
        return $prod;
    }

    public function searchProduction($name, $status)
    {

        $qb = $this->createQueryBuilder('p');
        $prod = $qb->select('p')
            ->addSelect("(CASE WHEN p.name like  '" . $name . "%'   THEN 1  WHEN p.name like '%" . $name . "'  THEN 2 ELSE 3 END) AS HIDDEN ordCol")
            ->where($qb->expr()->like('p.name', ':name'))
            ->setParameter('name', '%' . $name . '%')
            ->orderBy("p.name, ordCol");

        // Checked Prod
        if ($status == 0) {
            $prod->andWhere($qb->expr()->isNotNull('p.checkedAt'))
                ->andWhere($qb->expr()->isNull('p.archivedAt'));
            // Unchecked
        } else if ($status == 1) {
            $prod->andWhere($qb->expr()->isNull('p.checkedAt'))
                ->andWhere($qb->expr()->isNull('p.archivedAt'));
            // Archived
        } else {
            $prod->andWhere($qb->expr()->isNull('p.checkedAt'))
                ->andWhere($qb->expr()->isNotNull('p.archivedAt'));
        }


        return $qb->getQuery();
    }

    /**
     * @param $productionId
     * @return mixed
     */
    public function getProductionProfileIds($productionId)
    {
        $query = $this->_em->createQuery('
            SELECT
                profile.id as id
            FROM
                Theaterjobs\ProfileBundle\Entity\Profile profile
            LEFT JOIN
                profile.productionParticipations productionParticipations
            LEFT JOIN
                productionParticipations.production production
            WHERE production.id = :productionId')->setParameter('productionId', $productionId);

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }
}
