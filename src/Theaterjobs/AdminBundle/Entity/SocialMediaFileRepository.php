<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 22/02/2018
 * Time: 19:43
 */

namespace Theaterjobs\AdminBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class SocialMediaFileRepository extends EntityRepository
{

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getFirst()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->setMaxResults(1);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new NonUniqueResultException($e->getMessage());
        }
    }

}