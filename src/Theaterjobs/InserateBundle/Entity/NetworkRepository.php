<?php

namespace Theaterjobs\InserateBundle\Entity;

use DateTime;

/**
 * Repository for the Network.
 *
 * @category Repository
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class NetworkRepository extends InserateRepository {
    /*
     * List all networks by user
     */

    public function findAllNetworksByUser($user) {
        $qb = $this->createQueryBuilder('net');
        $news = $qb->innerJoin('net.user', 'user')
                ->where('user.id = :users')
                ->orderBy('net.createdAt', 'DESC')
                ->setParameter('users', $user)
                ->getQuery()
                ->getResult();
        return $news;
    }

    /*
     * List all networks by user
     */

    public function findNetworksFavourites($user) {
        $qb = $this->createQueryBuilder('net');
        $qb->innerJoin('net.userFavourite', 'uf')
                ->where('uf= :user')
                ->setParameter('user', $user);
        return $qb->getQuery()->getResult();
    }

    /*
     * List all network Activity by Network Id
     */

    public function findNetworkActivity($network, $qb) {
        $class = addslashes(get_class($network));
        $activity = $qb->select('activity')
                        ->from('TheaterjobsUserBundle:UserActivity', 'activity')
                        ->where('activity.entityId= :network_id')
                        ->andWhere('activity.entityClass LIKE :class')
                        ->orderBy('activity.createdAt', 'DESC')
                        ->setParameters(array('network_id' => $network->getId(), 'class' => '%' . $class))
                        ->getQuery()->getResult();

        return $activity;
    }

    public function countPublishedNetworksForDashboard($lastVisitDate, $actualDate) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $network = $qb->select('count(network.id) as network_published')
                ->from('TheaterjobsInserateBundle:Network', 'network')
                ->where('network.archivedAt IS NULL')
                ->andwhere('network.destroyedAt IS NULL')
                ->andWhere('network.publishedAt IS NOT NULL')
                ->andWhere('network.publishedAt BETWEEN :date_from AND :date_to')
                ->setParameters(array('date_from' => $lastVisitDate, "date_to" => $actualDate))
                ->getQuery()
                ->getResult();

        return $network;
    }

    public function prepareCategoriesForNetwork() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $categories = $qb->select('count(category.id) as category_total', 'category.id', 'category.slug', 'category.title')
                        ->from('TheaterjobsInserateBundle:Network', 'network')
                        ->innerJoin('network.categories', 'category')
                        ->where('network.archivedAt IS NULL')
                        ->andwhere('network.destroyedAt IS NULL')
                        ->andwhere('network.publishedAt IS NOT NULL')
                        ->andWhere('category.removedAt is NULL')
                        ->groupBy('category.id')
                        ->orderBy('category.title', 'ASC')
                        ->getQuery()->getResult();

        return $categories;
    }

    public function getNetworksByCategory($category) {
        $qb = $this->createQueryBuilder("p");
        $networks = $qb->innerJoin('p.categories', 'cat')
                ->where('p.archivedAt IS NULL')
                ->andwhere('p.destroyedAt IS NULL')
                ->andwhere('p.publishedAt IS NOT NULL')
                ->andWhere('cat.id= :categ')
                ->setParameter('categ', $category)
                ->getQuery()
                ->getResult();

        return $networks;
    }

    public function allNetworks() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $educations = $qb->select('COUNT(n.id)')
                        ->from('TheaterjobsInserateBundle:Network', 'n')
                        ->getQuery()->getResult();
        return $educations;
    }
    
    public function createNetworkQuery($form) {
        $searchData = $form->getData();
        $qb = $this->getEntityManager()->createQueryBuilder();

        $params = [];
        if ($searchData['dateFrom'] != $searchData['dateTo']) {
            $qb->select('SUBSTRING(j.createdAt, 1, 10) as dt,COUNT(j) as num');
        } else {
            $qb->select('SUBSTRING(j.createdAt, 1, 19) as dt,COUNT(j) as num');
        }

        $qb->from('TheaterjobsInserateBundle:Network', 'j');
        if ($searchData['status']) {
            if ($searchData['status'] == 'published') {
                $qb->andWhere('j.archivedAt IS NULL')
                        ->andWhere('j.destroyedAt IS NULL');
            }
            if ($searchData['status'] == 'archived') {
                $qb->andWhere('j.archivedAt IS NOT NULL OR j.destroyedAt IS NOT NULL');
            }
        }
        if ($searchData['dateFrom']) {
            $qb->andWhere('j.createdAt >= :startDate');
            $params['startDate'] = new DateTime($searchData['dateFrom']);
        }
        if ($searchData['dateTo']) {
            $qb->andWhere('j.createdAt < :endDate');
            $date = new DateTime($searchData['dateTo']);
            $date->modify('+1 day');
            $params['endDate'] = $date;
        }
        $qb->groupBy('dt');
        $qb->setParameters($params);

        $networks = $qb->getQuery()->getResult();
        return $networks;
    }

}
