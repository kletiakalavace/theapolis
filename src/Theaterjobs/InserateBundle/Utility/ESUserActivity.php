<?php

namespace Theaterjobs\InserateBundle\Utility;


use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Entity\UserActivity;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

trait ESUserActivity
{

    /**
     * @param $entityName
     * @param $id
     * @return mixed
     */
    public function getESUserActivity($entityName, $id)
    {
        $fosElastica = $this->container->get('fos_elastica.manager');
        $userActivityFinder = $this->container->get('fos_elastica.finder.events.activity');
        $queryUserActivity = $fosElastica->getRepository(UserActivity::class)->getEntityActivity($entityName, $id);
        $results = $userActivityFinder->createPaginatorAdapter($queryUserActivity);

        // show only 3 last user activity
        return $this->container->get('knp_paginator')->paginate($results, 1, 3);
    }

    /**
     * Shorthand to log activity
     *
     * @param $entity
     * @param $description
     * @param bool $forAdmin
     * @param null $changedFields
     * @param null $user
     * @param boolean $flush
     */
    public function logUserActivity($entity, $description, $forAdmin = false, $changedFields = null, $user = null, $flush = true)
    {
        $dispatcher = $this->get('event_dispatcher');
        $uacEvent = new UserActivityEvent($entity, $description, $forAdmin, $changedFields, $user, $flush);
        $dispatcher->dispatch("UserActivityEvent", $uacEvent);
    }
}