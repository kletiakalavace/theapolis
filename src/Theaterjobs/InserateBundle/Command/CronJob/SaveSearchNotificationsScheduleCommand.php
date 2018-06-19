<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\MainBundle\Entity\SaveSearch;
use Theaterjobs\MainBundle\Utility\CommandSchedulerInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\CommandExecuteTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;

/**
 *
 * Notify users for new published jobs and his saved search applies
 *
 * Class SaveSearchJobNotification
 */
class SaveSearchNotificationsScheduleCommand extends ContainerAwareCommand implements CronCommand, CommandSchedulerInterface
{
    use ScheduleEveryNight, ContainerTrait, CommandExecuteTrait;

    const CHUNK_SIZE = 100;
    const PROCESSING_JOB = "theaterjobs:cron:saved-search:notifications";

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('theaterjobs:cron:schedule:save-search:notifications')
            ->setDescription('Sends notification to users about new jobs matching the user\'s saved searches!');
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $saveSearches = $em->getRepository(SaveSearch::class)->getCronJobs();
        return $this->concatIds($saveSearches);
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

    /**
     * @param $array
     * @return mixed
     */
    private function concatIds($array){
        return array_reduce($array, function ($acc, $item) {
            $acc[] = $item["id"];
            return $acc;
        }, []);
    }
}