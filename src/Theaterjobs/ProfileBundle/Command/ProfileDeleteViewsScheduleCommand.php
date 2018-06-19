<?php

namespace Theaterjobs\ProfileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputOption;
use Theaterjobs\MainBundle\Utility\CommandSchedulerInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\CommandExecuteTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleMonthly;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Delete all views prior than 10 days of when this command is being run.
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class ProfileDeleteViewsScheduleCommand extends ContainerAwareCommand implements CronCommand, CommandSchedulerInterface
{
    use ScheduleMonthly, ContainerTrait, CommandExecuteTrait;

    const PROCESSING_JOB = 'theaterjobs:cron:profile:delete:views';
    const CHUNK_SIZE = 1000;

    /**
     * @inheritdoc
     */
    protected function configure() {
        $this->setName('theaterjobs:cron:schedule:profile:delete:views')
            ->setDescription('Schedule command to notify non member user for profile views')
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Simulate a day');
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        return $em->getRepository(Profile::class)->publishedProfileIds();
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return self::PROCESSING_JOB;
    }

    /**
     * @return integer
     */
    public function getChunkSize()
    {
        return self::CHUNK_SIZE;
    }
}