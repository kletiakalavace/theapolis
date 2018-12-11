<?php

namespace Theaterjobs\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Theaterjobs\UserBundle\Entity\AuthenticationLogs;
class LoginListener
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

   public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
   {
       $user = $event->getAuthenticationToken()->getUser();
       $session=new Session();
       $session->set('lastLogin',$user->getLastLogin());

       $em = $this->container->get('doctrine.orm.entity_manager');

       $dateMinus15Days = date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 15, date("Y")));
       $qb = $em->createQueryBuilder();
       $queryDeleteOlderThan15DaysLogins = $qb->delete('TheaterjobsUserBundle:AuthenticationLogs', 'ec')
           ->where($qb->expr()->eq('ec.createdBy', ':id'))
           ->andWHere('ec.loginDate < :date')
           ->setParameters(array('id' =>$user->getId(), 'date' => $dateMinus15Days))
           ->getQuery();
       $queryDeleteOlderThan15DaysLogins->execute();
       $user->setLoginCounter($user->getLoginCounter()+1);
       $user->setOnline(true);
       $newSuccesfulLogin = new AuthenticationLogs();
       $newSuccesfulLogin->setCreatedBy($user);
       $newSuccesfulLogin->setLoginDate(new \DateTime());
       $em->persist($user);
       $em->persist($newSuccesfulLogin);
       $em->flush();

       $queryBuilder =$em->createQueryBuilder();
       $queryBuilder->select('DISTINCT v.foreignKey')
           ->from('TheaterjobsStatsBundle:View','v')
           ->where('v.user = :user')
           ->andWhere('v.objectClass LIKE :objectClass')
           ->setParameters(array('user'=>$user,'objectClass'=>'%job'));
       $jobIds = $queryBuilder->getQuery()->getResult();
       $ids = [];
       foreach($jobIds as $id)
       {
           $ids[] = $id['foreignKey'];

       }
       $this->container->get('session')->set('jobIds',$ids);
       $key = sha1($user->getUsername() . $user->getSalt());// . $user->getLastLogin()->format('Y-m-d H:i:s'));
       $value = array('userId'=>$user->getId(),'username'=>$user->getUsername());
       $value = json_encode($value);

       $redis = $this->container->get('snc_redis.default');
       $redis->hset($key,'userid',$user->getId());
       $redis->hset($key,'username',$user->getUsername());
       $redis->expire($key, 93600);

       $this->container->get('session')->set('realtime_token',$key);
       $session->set('realtime_token',$key);
        
   }

}