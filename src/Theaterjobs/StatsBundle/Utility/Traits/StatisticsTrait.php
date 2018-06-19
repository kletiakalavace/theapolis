<?php

namespace Theaterjobs\StatsBundle\Utility\Traits;

use Carbon\Carbon;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Event\ViewEvent;
use Theaterjobs\StatsBundle\StatsEvents;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Trait StatisticsTrait
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @package Theaterjobs\StatsBundle\Utility\Traits
 */
trait StatisticsTrait
{
    /**
     * @param $entityName
     * @param $id
     * @return mixed
     */
    public function countWeeklyViews($entityName, $id)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $lastWeek = Carbon::now()->subWeek(1)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $lastWeek);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $entityName
     * @param $id
     * @return mixed
     */
    public function countMonthlyViews($entityName, $id)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $lastWeek = Carbon::now()->subMonth(1)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $lastWeek);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $entityName
     * @param $id
     * @return mixed
     */
    public function countAllViews($entityName, $id)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $entityName
     * @param $id
     * @param $since
     * @return mixed
     */
    public function countAllViewsSince($entityName, $id, $since)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $since->format('Y-m-d'));
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $className
     * @param $fk
     * @param User $user
     * @param bool $doNotTrack
     */
    public function viewEvent($className, $fk, User $user = null, $doNotTrack = false)
    {
        $dispatcher = $this->get('event_dispatcher');
        $event = (new ViewEvent())
            ->setUser($user)
            ->setClassName($className)
            ->setFk($fk)
            ->setDoNotTrack($doNotTrack);
        $dispatcher->dispatch(StatsEvents::STATS_VIEW, $event);
    }

    /**
     * @param $entityName
     * @param $id
     * @param $nr
     * @return mixed
     */
    public function countViewsSinceDays($entityName, $id, $nr)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $since = Carbon::now()->subDays($nr)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $since);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $entityName
     * @param $id
     * @param $nr
     * @return mixed
     */
    public function countViewsSinceWeeks($entityName, $id, $nr)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $since = Carbon::now()->subWeeks($nr)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $since);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }

    /**
     * @param $entityName
     * @param $id
     * @param $nr
     * @return mixed
     */
    public function countViewsSinceMonths($entityName, $id, $nr)
    {
        $finder = $this->get('fos_elastica.index.events.view');
        $since = Carbon::now()->subMonths($nr)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getEntityViews($entityName, $id, $since);
        $results = $finder->search($query);
        return $results->getTotalHits();
    }
}