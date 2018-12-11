<?php

namespace Theaterjobs\ProfileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleMonthly;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\UserBundle\Event\NotificationEvent;
use Theaterjobs\UserBundle\Entity\Notification;
use Carbon\Carbon;

/**
 * Check command to notify a user (non member) about his profile visits monthly
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class ProfileViewsNotificationCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleMonthly;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:profile-views')
            ->setDescription('Notify non member user for profile views')
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Simulate a day');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileIds = [];
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $finder = $this->getContainer()->get('fos_elastica.index.events.view');
        $lastMonth = Carbon::now()->subMonth(1)->format('Y-m-d');
        $query = $this->getContainer()->get('fos_elastica.manager')->getRepository(View::class)->getViewsByEntity($finder, Profile::class, $lastMonth);
        $results = $finder->search($query);
        $aggs = $results->getAggregation('objectClass');

        if (!$aggs['buckets']) {
            $output->writeln("No views.");
            return;
        };


        foreach ($aggs['buckets'] as $agg) {
            $profileIds[] = $agg['key'];
        }

        $profiles = $em->getRepository(Profile::class)->findById($profileIds);
        $profiles = $this->matchIdWithEntity($profiles);
        $nr = count($aggs['buckets']);

        $batchSize = 30;
        for ($i = 0; $i < $nr; $i++) {
            $agg = $aggs['buckets'][$i];
            $j = $agg['key'];
            if ($agg['doc_count']) {
                $this->sendNotification($agg['doc_count'], $profiles[$j]);
                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear(Notification::class);
                }
            }
        }
        $em->flush();
        $em->clear();
        $output->writeln("Sent notification to all users");
    }

    /**
     * Send notification, profile visits to user
     *
     * @param $stats
     * @param $profile
     */
    private function sendNotification($stats, $profile)
    {
        $notification = new Notification();
        $title = 'dashboard.notification.number.profile.views %number%';
        $transParams = array('%number%' => $stats);

        $notification->setTitle($title)
            ->setCreatedAt(Carbon::now())
            ->setDescription("")
            ->setTranslationKeys($transParams)
            ->setLink('tj_profile_profile_show')
            ->setLinkKeys(['slug' => $profile->getSlug()])
            ->setRequireAction(false);

        $event = (new NotificationEvent())
            ->setObjectClass(Profile::class)
            ->setObjectId($profile->getId())
            ->setNotification($notification)
            ->setUsers($profile->getUser())
            ->setType('profile_old_actuality')
            ->setFlush(false);

        $this->getContainer()->get('event_dispatcher')->dispatch('notification', $event);
    }

    /**
     * match index key with entity for direct access
     * @param Profile[] $entities
     *
     * @return Profile[]
     */
    private function matchIdWithEntity($entities)
    {
        $newsEntities = [];
        foreach ($entities as $entity) {
            $newsEntities[$entity->getId()] = $entity;
        }
        return $newsEntities;
    }

}
