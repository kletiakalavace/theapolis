<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 22/03/2018
 * Time: 20:12
 */

namespace Theaterjobs\AdminBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Theaterjobs\AdminBundle\Model\SocialMediaSearch;

class SocialMediaRepository extends EntityRepository
{

    /**
     * @param SocialMediaSearch $adminSocialMediaSearch
     * @return \Doctrine\ORM\Query
     */
    public function adminListSearch(SocialMediaSearch $adminSocialMediaSearch)
    {
        $qb = $this->createQueryBuilder('sc');

        if ($adminSocialMediaSearch->getOrderCol()) {
            $qb->orderBy(sprintf("sc.%s", $adminSocialMediaSearch->getOrderCol()), $adminSocialMediaSearch->getOrder());
        }

        return $qb->getQuery();
    }

}