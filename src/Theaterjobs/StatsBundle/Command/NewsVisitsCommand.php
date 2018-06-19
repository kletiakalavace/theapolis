<?php

namespace Theaterjobs\StatsBundle\Command;


use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleMonthly;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\StatsBundle\Entity\View;

/**
 * Counts all news visits monthly
 * Class NewsVisitsCommand
 * @package Theaterjobs\NewsBundle\Command
 */
class NewsVisitsCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleMonthly, ContainerTrait, ScheduleUpdateESIndexTrait;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('theaterjobs:cron:news-visits')
            ->setDescription('Command to count news visits monthly');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $finder = $this->get('fos_elastica.index.events.view');
        $lastMonth = Carbon::now()->subMonth(1)->format('Y-m-d');
        $query = $this->get('fos_elastica.manager')->getRepository(View::class)->getViewsByEntity(News::class, $lastMonth);
        $results = $finder->search($query);
        $aggs = $results->getAggregation('objectClass');
        $newsIds = [];

        if (!$aggs['buckets']) {
            $output->writeln("No news to update.");
            return;
        };

        foreach ($aggs['buckets'] as $agg) {
            $newsIds[] = $agg['key'];
        }

        $newsEntities = $em->getRepository(News::class)->findById($newsIds);
        $newsEntities = $this->matchIdWithEntity($newsEntities);

        foreach ($aggs['buckets'] as $agg) {
            $i = $agg['key'];
            $total = $newsEntities[$i]->getTotalViews() + $agg['doc_count'];
            $newsEntities[$i]->setTotalViews($total);
            $em->persist($newsEntities[$i]);
        }

        $ids = $em->getRepository(View::class)->getDeleteObjectViewsBeforeIds(News::class, $lastMonth);
        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, View::class, $ids, 'cron');

        $em->flush();
        $output->writeln(sprintf("Deleted %d views", count($ids)));
        $output->writeln(sprintf("Updated %d news", count($newsIds)));
    }

    /**
     * match index key with entity for direct access
     * @param News[] $entities
     *
     * @return News[]
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