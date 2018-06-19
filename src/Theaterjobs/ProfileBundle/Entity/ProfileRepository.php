<?php

namespace Theaterjobs\ProfileBundle\Entity;

use Carbon\Carbon;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Theaterjobs\ProfileBundle\Model\UserInterface;
use Theaterjobs\StatsBundle\Entity\View;

/**
 * ProfileRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProfileRepository extends EntityRepository
{
    public function findOneByUser(UserInterface $user)
    {
        return $user->getProfile();
    }

    /**
     * Finds the profiles based on search parameters
     *
     * @param type $parentCategory The parent category if any
     * @param type $subCategory The sub category if any
     * @param type $term The keywords used in the search if any
     * @return ArrayColection The Profiles found
     */
    public function findBySearch($parentCategory = null, $subCategory = null, $term = null)
    {

        if ($parentCategory === null && $subCategory === null && $term === null)
            return $this->findByRandomOffset(28);

        $qb = $this->_em->createQueryBuilder();

        $qb->select('profile')
            ->from('TheaterjobsProfileBundle:Profile', 'profile');

        if ($subCategory !== null) {
            $qb->innerJoin('profile.categories', 'cat')
                ->where('cat.slug=:Slug')
                ->setParameter('Slug', $subCategory);
        } else {
            if ($parentCategory !== null) {
                $qb->innerJoin('profile.categories', 'cat')
                    ->innerJoin('cat.parent', 'parent')
                    ->where('parent.slug=:parentSlug')
                    ->setParameter('parentSlug', $parentCategory);
            } else {
                $qb->leftJoin('profile.categories', 'cat');
            }
        }

        if ($term !== null) {
            if ($parentCategory === null)
                $qb->Where("profile.firstName LIKE '%$term%'");
            else
                $qb->andWhere("profile.firstName LIKE '%$term%'");

            $qb->orWhere("profile.lastName LIKE '%$term%'")
                ->orWhere("profile.biography LIKE '%$term%'")
                ->orWhere("cat.title LIKE '%$term%'");
        }

        return $qb->getQuery()->getResult();
    }

    public function findByRandomOffset($amount)
    {
        $rows = $this->_em->createQuery('SELECT COUNT(p.id) FROM TheaterjobsProfileBundle:Profile p')->getSingleScalarResult();
        $offset = max(0, rand(0, $rows - $amount - 1));

        $query = $this->_em->createQuery('
                SELECT DISTINCT p
                FROM TheaterjobsProfileBundle:Profile p')
            ->setMaxResults($amount)
            ->setFirstResult($offset);
        return $query->getResult();
    }

    public function findTotalViews($profile)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(view.object) as total_view')
            ->from('TheaterjobsStatsBundle:ProfileView', 'view')
            ->where('view.object = ?1')
            ->groupBy('view.object')
            ->setParameter(1, $profile->getId());
        $totalViews = $qb->getQuery()->getResult();
        return $totalViews;
    }

    public function findLastWeekViews($profile)
    {
        $format = 'Y-m-j G:i:s';
        $date = date($format);
        $dat = date($format, strtotime('-1 week' . $date));
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(view.object) as last_week_view')
            ->from('TheaterjobsStatsBundle:ProfileView', 'view')
            ->where('view.object = ?1')
            ->andWhere("view.datetime>= :date")
            ->setParameters(array(1 => $profile->getId(), 'date' => $dat));
        $lastWeekViews = $qb->getQuery()->getResult();
        return $lastWeekViews;
    }

    public function findAllLikeName($name)
    {
        $qb = $this->createQueryBuilder("p");
        $qb->where($qb->expr()->like('p.firstName', ':name'))
            ->setParameter(':name', '%' . $name . '%');

        return $qb->getQuery()->getResult();
    }

    public function countForDashboard($lastVisitDate, $actualDate)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $profiles = $qb->select('count(profile.id) as profiles')
            ->from('TheaterjobsProfileBundle:Profile', 'profile')
            ->where('profile.isPublished= :param')
            ->andWhere('profile.confirmedAt BETWEEN :date_from AND :curr_date')
            ->andWhere('profile.revokedAt IS NULL')
            ->setParameters(array('param' => true, 'date_from' => $lastVisitDate, 'curr_date' => $actualDate))
            ->getQuery()->getResult();
        return $profiles;
    }

    public function findByCounts($em)
    {
        $sql = "select categoryinterface_id as id,count(categoryinterface_id) as profile_num from tj_profile_profiles_categories ic
left join tj_profile_profiles i on ic.profile_id = i.id
group by categoryinterface_id
";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $count = $stmt->fetchAll();

        //exit();

        $sql = 'SELECT parent as id ,count(parent) as job_num FROM
(SELECT profile_id,categoryinterface_id,c.parent_id as parent FROM tj_profile_profiles_categories ic
LEFT JOIN tj_category_categories c ON ic.categoryinterface_id = c.id
INNER JOIN tj_profile_profiles j ON profile_id = j.id
GROUP BY categoryinterface_id,profile_id
 )as jobs_categoryParent
GROUP BY parent';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $count_parent = $stmt->fetchAll();

        $job_num = array();
        foreach ($count as $x)
            $job_num[$x['id']] = $x['profile_num'];
        foreach ($count_parent as $i)
            $job_num[$i['id']] = $i['job_num'];

        return $job_num;
//        dump($job_num);
//                exit();
    }

    public function userSuggest($word, $qb)
    {
        $users = $qb->select('users')
            ->from('TheaterjobsProfileBundle:Profile', 'users')
            ->where('users.firstName LIKE :firstName')
            ->orWhere('users.lastName LIKE :lastName')
            ->setParameters(array('firstName' => '%' . $word . '%', 'lastName' => '%' . $word . '%'))
            ->getQuery()->getResult();
        return $users;
    }

    public function createProfileQuery($params)
    {
        $qb = $this->createQueryBuilder("j");
        $str = [];
        $str[] = 'j.slug';
        $str[] = 'u.email';
        $str[] = 'u.roles';
        $str[] = 'u.username';
        foreach ($params->get('fields_to_show') as $param) {
            if ($param != 'views' && $param != 'lastLogin' && $param != "email" && $param != "roles" && $param != 'organizationMember' && $param != "contact" && $param != "adminComment" && $param != 'profilStatus')
                $str[] = 'j.' . $param;
            if ($param == 'lastLogin') {
                $str[] = 'u.lastLogin';
            }
            if ($param == 'views' && count($params->get('fields_to_search')) && in_array("views", $params->get('fields_to_search')))
                $str[] = 'COUNT(v.id) AS Views';
        }
        $qb->select($str)->innerJoin('j.user', 'u');
        $parameters = [];

        if ($params->get('fields_to_search') !== null) {
            foreach ($params->get('fields_to_search') as $param) {
                if ($param == "email") {
                    $qb->andWhere("u." . $param . ' LIKE :' . $param);
                } elseif ($param == "roles") {
                    $qb->andWhere("u." . $param . ' LIKE :' . $param);
                } elseif ($param == "createdAt") {
                    $qb->andWhere("j." . $param . ' >= :startDate');
                    $qb->andWhere("j." . $param . ' <= :endDate');
                } elseif ($param == 'organizationMember') {
                    $qb->innerJoin('u.userOrganizations', 'org');
                } elseif ($param == 'contact') {
                    $qb->innerJoin('j.contactSection', 'c');
                    $qb->andWhere('c.' . $param . ' LIKE :' . $param);
                } elseif ($param == 'adminComment') {
                    $qb->innerJoin('u.adminComments', 'ac')
                        ->andWhere('ac.description' . ' LIKE :' . $param);
                } elseif ($param == 'profilStatus') {
                    if ($params->get($param) == "published")
                        $qb->andWhere('j.isPublished=true');
                    if ($params->get($param) == "blocked") {
                        $qb->andWhere('j.isPublished=false');
                        $qb->andWhere('j.inAdminCheckList=true');
                    }
                    if ($params->get($param) == "unpublished")
                        $qb->andWhere('j.isPublished=false');
                } elseif ($param == "lastLogin") {
                    $qb->andWhere("u." . $param . ' >= :startDate');
                    $qb->andWhere("u." . $param . ' <= :endDate');
                } elseif ($param == "updatedAt") {
                    $qb->andWhere("j." . $param . ' >= :updatedAtFrom');
                    $qb->andWhere("j." . $param . ' <= :updatedAtTo');
                } elseif ($param == 'views') {
                    $qb->innerJoin('TheaterjobsStatsBundle:View', 'v', \Doctrine\ORM\Query\Expr\Join::WITH, 'j.id = v.foreignKey');
                    $qb->andWhere("v.createdAt" . ' >= :viewsFrom');
                    $qb->andWhere("v.createdAt" . ' <= :viewsTo');
                    $qb->andWhere("v.objectClass LIKE :class");
                    $parameters['class'] = '%Profile%';

                    $qb->addGroupBy('v.foreignKey');
                    if ($params->get('viewsFrom') != '') {
                        $date = new \DateTime($params->get('viewsFrom'));
                        $parameters['viewsFrom'] = $date->format('Y-m-d H:i:s');
                    } else {
                        $date = new \DateTime();
                        $parameters['viewsFrom'] = $date->format('Y-m-d H:i:s');
                    }
                    if ($params->get('viewsTo') != '') {
                        $date = new \DateTime($params->get('viewsTo'));
                        $date->modify('+1 day');
                        $parameters['viewsTo'] = $date->format('Y-m-d H:i:s');
                    } else {
                        $date = new \DateTime();
                        $parameters['viewsTo'] = $date->format('Y-m-d H:i:s');
                    }
                } else {
                    $qb->andWhere('j.' . $param . ' LIKE :' . $param);
                }

                if ($param != "updatedAt" && $param != "createdAt" && $param != 'profilStatus' && $param != 'lastLogin' && $param != 'views') {
                    $parameters[$param] = '%' . $params->get($param) . '%';
                } else {
                    if ($param == 'createdAt') {
                        if ($params->get('createdAtFrom') != '') {
                            $date = new \DateTime($params->get('createdAtFrom'));
                            $parameters['startDate'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $parameters['startDate'] = '';
                        }
                        if ($params->get('createdAtTo') != '') {
                            $date = new \DateTime($params->get('createdAtTo'));
                            $date->modify('+1 day');
                            $parameters['endDate'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $date = new \DateTime();
                            $date->modify('+1 day');
                            $parameters['endDate'] = $date->format('Y-m-d H:i:s');
                        }
                    }
                    if ($param == 'lastLogin') {
                        if ($params->get('lastLoginFrom') != '') {
                            $date = new \DateTime($params->get('lastLoginFrom'));
                            $parameters['startDate'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $parameters['startDate'] = '';
                        }
                        if ($params->get('lastLoginTo') != '') {
                            $date = new \DateTime($params->get('lastLoginTo'));
                            $date->modify('+1 day');
                            $parameters['endDate'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $date = new \DateTime();
                            $date->modify('+1 day');
                            $parameters['endDate'] = $date->format('Y-m-d H:i:s');
                        }
                    }
                    if ($param == 'updatedAt') {
                        if ($params->get('updatedAtFrom') != '') {
                            $date = new \DateTime($params->get('updatedAtFrom'));
                            $parameters['updatedAtFrom'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $parameters['updatedAtFrom'] = '';
                        }
                        if ($params->get('updatedAtTo') != '') {
                            $date = new \DateTime($params->get('updatedAtTo'));
                            $date->modify('+1 day');
                            $parameters['updatedAtTo'] = $date->format('Y-m-d H:i:s');
                        } else {
                            $date = new \DateTime();
                            $date->modify('+1 day');
                            $parameters['updatedAtTo'] = $date->format('Y-m-d H:i:s');
                        }
                    }
                }
            }
        }
        unset($parameters['organizationMember']);
        unset($parameters['profilStatus']);

        $qb->setParameters($parameters);
        $entities = $qb->getQuery()->getResult();
        if (count($entities) === 1 && $entities[0]['slug'] === null)
            unset($entities[0]);
        return $entities;
    }


    public function getQualificationByCategory($slug)
    {
        $qualification = $this->getEntityManager()->createQueryBuilder()
            ->select('cat.slug')
            ->from('TheaterjobsProfileBundle:Qualification', 'q')
            ->innerJoin('q.categories', 'cat')
            ->where('cat.slug LIKE :slug')
            ->setParameter('slug', "%" . $slug . "%");

        return $qualification->getQuery()->getResult();
    }

    public function getRateableSkills($id)
    {
        $qb = $this->createQueryBuilder('profile');
        $qb->select('skill.title,languageSkill.rating');
        $qb->innerJoin('profile.skillSection', 'ss')
            ->innerJoin('ss.languageSkill', 'languageSkill')
            ->innerJoin('languageSkill.skill', 'skill')
            ->where($qb->expr()->eq('profile.id', $id));
        $qb->addOrderBy('languageSkill.id', 'ASC');
        return $qb->getQuery()->getResult();

    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countPublishedProfiles()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p.id)')
            ->where('p.isPublished= 1');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Users that their profile updatedAt is older than $time days
     * @param int $days
     *
     * @return array
     */
    public function olderThan($days)
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->select('p.id')
            ->where('p.lastUpdate < :date')
            ->andWhere('p.isPublished = true')
            ->setParameters([
                'date' => Carbon::now()->subDays($days)
            ])
            ->getQuery();
        return $query->getResult();
    }

    /**
     * @param Profile $profile
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countProfilePhoto(Profile $profile)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(m.id)')
            ->innerJoin('p.mediaImage', 'm')
            ->where('p.id = :id')
            ->andWhere('m.isProfilePhoto = :isProfilePhoto')
            ->setParameters([
                'id' => $profile->getId(),
                'isProfilePhoto' => 1
            ]);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }


    /**
     * @param Profile $profile
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countProfileQualifications(Profile $profile)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(qualifications.id)')
            ->innerJoin('p.qualificationSection', 'qualificationSection')
            ->innerJoin('qualificationSection.qualifications', 'qualifications')
            ->where('p.id = :id')
            ->setParameter('id', $profile->getId());

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Profile $profile
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countProfileProductionParticipators(Profile $profile)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(productionParticipations.id)')
            ->innerJoin('p.productionParticipations', 'productionParticipations')
            ->where('p.id = :id')
            ->setParameter('id', $profile->getId());

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Profile $profile
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countProfileExperiences(Profile $profile)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(e.id)')
            ->innerJoin('p.experience', 'e')
            ->where('p.id = :id')
            ->setParameter('id', $profile->getId());

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Update profile views based on views table records
     * @param Carbon $since
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateProfileViews($since, $profileIds)
    {
        $profileTableName = $this->_em->getClassMetadata(Profile::class)->getTableName();
        $viewTableName = $this->_em->getClassMetadata(View::class)->getTableName();
        $profileClassName = addslashes(Profile::class);
        $since = $since->format('Y-m-d');
        $profileIds = implode(',', $profileIds);

        $sql ="UPDATE $profileTableName p set p.total_views = p.total_views + 
                  (SELECT count(id) FROM $viewTableName v
                  WHERE v.foreign_key = p.id and
                  v.object_class = '$profileClassName' and v.createdAt < date('$since') ) 
                  where p.id in ($profileIds)";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get profile ids of published profiles
     * @return mixed
     */
    public function publishedProfileIds()
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->select('p.id as id')
            ->where('p.isPublished = false')
            ->getQuery();

        $result = $query->getResult();
        return array_reduce($result, function($acc, $item) {
            $acc[] = $item['id'];
            return $acc;
        }, []);
    }
}
