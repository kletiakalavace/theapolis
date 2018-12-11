<?php

namespace Theaterjobs\UserBundle\Entity;

use Carbon\Carbon;
use Doctrine\ORM\EntityRepository;
use Theaterjobs\ProfileBundle\Entity\MediaImage;

/**
 * Description of UserRepository
 *
 * @author abame
 */
class UserRepository extends EntityRepository {



    public function getAdminUsers() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $admin = $qb->select('u')
                        ->from('TheaterjobsUserBundle:User', 'u')
                        ->where('u.roles LIKE :roles')
                        ->setParameter('roles', '%"ROLE_ADMIN"%')
                        ->getQuery()->getResult();
        return $admin;
    }
    
    public function allRegisteredUsers(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $users = $qb->select('COUNT(u.id)')
                ->from('TheaterjobsUserBundle:User','u')
                ->getQuery()->getSingleScalarResult();
        return $users;
    }
    
    public function allMembers(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $members = $qb->select('COUNT(u.id)')
                ->from('TheaterjobsUserBundle:User','u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%"ROLE_MEMBER"%')
                ->getQuery()->getSingleScalarResult();

        return $members;
    }

    public function countAllOnlineMembers(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $members = $qb->select('COUNT(u.id)')
            ->from('TheaterjobsUserBundle:User','u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.online = 1')
            ->setParameter('roles', '%"ROLE_MEMBER"%')
            ->getQuery()->getSingleScalarResult();

        return $members;
    }

    public function allAdmins(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $admins = $qb->select('COUNT(u.id)')
                ->from('TheaterjobsUserBundle:User','u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%"ROLE_ADMIN"%')
                ->getQuery()->getResult();
        return $admins;
    }

    public function allOnlineAdmins(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $admins = $qb->select('u')
            ->from('TheaterjobsUserBundle:User','u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.online = 1')
            ->setParameter('roles', '%"ROLE_ADMIN"%')
            ->getQuery()->getResult();
        return $admins;
    }
    
    public function userWithOrga(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $uTeam = $qb->select($qb->expr()->countDistinct('t.user'))
                ->from('TheaterjobsUserBundle:UserOrganization','t')
                ->getQuery()->getResult();
        return $uTeam;
    }
    
    public function getAdminUser(){
        $usersQb = $this->getEntityManager()->createQueryBuilder();
        $adminUsers = $usersQb->select('users')->from('TheaterjobsUserBundle:User', 'users')
                        ->where($usersQb->expr()->like('users.roles', ':role'))
                        ->setParameters(array('role' => '%ROLE_ADMIN%'));
        
        return $adminUsers->getQuery()->getResult();
    }

    public function getFailedAuthAttempts($client_ip){

        $date = date("Y-m-d H:i:s");
        $time = strtotime($date);
        $time = $time - (16 * 60);
        $date = date("Y-m-d H:i:s", $time);
        $qb = $this->getEntityManager()->createQueryBuilder();

        $loginAttemptsFromIp = $qb->select('count(attempt.id)')
            ->from('TheaterjobsUserBundle:LoginAttempts', 'attempt')
            ->where('attempt.ipAddress= :ip')
            ->andWhere('attempt.loginAttemptDate >= :date')
            ->setParameters(array('ip' => $client_ip, 'date' => $date))
            ->getQuery()->getSingleResult();


        return count($loginAttemptsFromIp);
    }

    public function checkRegisteredButNotConfirmed($email){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $emails = $qb->select('u')
            ->from('TheaterjobsUserBundle:User', 'u')
            ->where($qb->expr()->eq('u.email', ':email'))
            ->andWHere('u.confirmationToken is not NULL')
            ->setParameter('email', $email)
            ->getQuery()->getResult();
        return $emails;
    }

    public function checkForActiveChangeEmailRequest($id){

        $qb = $this->getEntityManager()->createQueryBuilder();
        $emails = $qb->select('ec')
            ->from('TheaterjobsUserBundle:EmailChangeRequest', 'ec')
            ->where($qb->expr()->eq('ec.userId', ':id'))
            ->setParameter('id', $id)
            ->getQuery()->getResult();

        return $emails;
    }

    public function checkForActiveNameChangeRequest($id){

        $qb = $this->getEntityManager()->createQueryBuilder();
        $emails = $qb->select('ec')
            ->from('TheaterjobsUserBundle:NameChangeRequest', 'ec')
            ->where($qb->expr()->eq('ec.createdBy', ':id'))
            ->andWhere('ec.status = 0')
            ->setParameter('id', $id)
            ->getQuery()->getResult();

        return $emails;
    }


    public function getLastTenDaysAuthentications($id){

        $date = date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 10, date("Y")));

        $qb = $this->getEntityManager()->createQueryBuilder();
        $loginCounter = $qb->select('ec')
            ->from('TheaterjobsUserBundle:AuthenticationLogs', 'ec')
            ->where($qb->expr()->eq('ec.createdBy', ':id'))
            ->andWHere('ec.loginDate >= :date')
            ->setParameters(array('id' => $id, 'date' => $date))
            ->getQuery()->getResult();

        return $loginCounter;
    }

    public function deleteBefore15DaysAuthenticationLogs($id){

        $date = date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 15, date("Y")));

        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->delete('TheaterjobsUserBundle:AuthenticationLogs', 'ec')
            ->where($qb->expr()->eq('ec.createdBy', ':id'))
            ->andWHere('ec.loginDate < :date')
            ->setParameters(array('id' => $id, 'date' => $date))
            ->getQuery();
            return $query->execute();
    }

     public function setUserOffline($id){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->update('TheaterjobsUserBundle:User', 'ec')
            ->set('ec.online', false)
            ->where($qb->expr()->eq('ec.id', ':id'))
            ->setParameter('id' , $id)
            ->getQuery();
            return $query->execute();
    }

    /**
     * Get rand published prof images
     *
     * @return array
     */
    public function getRandomProfileImages(){
        $nrProfImg = 18;
        $qb = $this->_em->createQueryBuilder();

        $randProf = $qb->select('i')
            ->from(MediaImage::class, 'i')
            ->addSelect('RAND() as HIDDEN rand')
            ->innerJoin('i.profile', 'p')
            ->where('i.isProfilePhoto = 1')
            ->andWhere('p.isPublished = 1')
            ->orderBy('rand')
            ->setMaxResults($nrProfImg)
            ->getQuery()->getResult();
        return $randProf;
    }

    /**
     * Find all users by his role
     *
     * @param $role
     * @return User[]
     */
    public function findByRole($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }

    /**
     * All users that have quited contract in 6 weeks period
     * @return User[]
     */
    public function recurringUsers() {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from('TheaterjobsUserBundle:User', 'u')
            ->where('u.hasRequiredRecuringPaymentCancel = 1')
            ->where('u.quitContract = 1');
        return $qb->getQuery()->getResult();
    }

    /**
     * All users that they membership expires today and have recurring payment
     * @return User[]
     */
    public function getRecurringUsersExpireToday() {
        $qb = $this->_em->createQueryBuilder();
        $user = $qb->select('u')
            ->from('TheaterjobsUserBundle:User', 'u')
            ->where('u.membershipExpiresAt = :date')
            ->andWhere('u.recuringPayment = true')
            ->setParameter('date', Carbon::today())
            ->getQuery()->getResult();
        return $user;
    }

    /**
     * Returns all users that they membership expires sooner than the param
     *
     * @param  Carbon $today
     * @param  Carbon $after
     * @return User[]
     */
    public function membershipExpires($today, $after)
    {
        $qb = $this->createQueryBuilder('u');
        $query = $qb->where('u.membershipExpiresAt < :date and u.membershipExpiresAt > :today')
            ->andWhere('u.quitContract = false')
            ->setParameters(['date' => $after, 'today' => $today])
            ->getQuery();

        return $query->getResult();
    }
}