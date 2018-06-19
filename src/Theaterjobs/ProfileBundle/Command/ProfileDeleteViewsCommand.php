<?php

namespace Theaterjobs\ProfileBundle\Command;

use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleMonthly;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\StatsBundle\Entity\View;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;

/**
 * Delete all views prior than 10 days of when this command is being run.
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class ProfileDeleteViewsCommand extends ContainerAwareCommand
{
    use StatisticsTrait, ContainerTrait, ScheduleUpdateESIndexTrait;

    /**
     * @inheritdoc
     */
    protected function configure() {
        $this->setName('theaterjobs:cron:profile:delete:views')
            ->addArgument('profileIds', InputArgument::REQUIRED, 'Profile ids to process')
            ->setDescription('Delete views');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileIds = json_decode($input->getArgument('profileIds'));
        $tenDaysAgo =  Carbon::today()->subDays(10);
        $em = $this->get('doctrine.orm.entity_manager');
        $em->getRepository(Profile::class)->updateProfileViews($tenDaysAgo, $profileIds);
        $output->writeln('Updated Profile Views in DB');
        $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, Profile::class, $profileIds, 'cron', true);
        $output->writeln('Updated Profile Views in ES');

        $viewIds = $em->getRepository(View::class)->getDeleteObjectViewsBeforeIds(Profile::class, $tenDaysAgo, $profileIds);
        $output->writeln('Deleted Views of type Profile in DB');
        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, View::class, $viewIds, 'cron', true);
        $output->writeln('Deleted Views of type Profile in ES');
    }
}