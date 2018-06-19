<?php

namespace Theaterjobs\ProfileBundle\Command\ProfileUpdateCommand;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\MainBundle\Utility\CommandSchedulerInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\CommandExecuteTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Send a notification to users with profile older than 100 days to update profile
 *
 * @package Theaterjobs\ProfileBundle\Command
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class ProfileUpdateScheduleCommand extends ContainerAwareCommand implements CronCommand, CommandSchedulerInterface
{
    use ScheduleEveryNight, ContainerTrait, CommandExecuteTrait;

    const CHUNK_SIZE = 100;
    const PROCESSING_JOB = "theaterjobs:cron:profile-update:notifications";

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:schedule:profile-update:notifications')
            ->setDescription('Loads jobs to queue for notifying users');
    }

    /**
     * @return array
     */
    public function getIds()
    {
        // Days
        $olderThan = 100;
        $em = $this->get('doctrine.orm.entity_manager');

        $profiles = $em->getRepository(Profile::class)->olderThan($olderThan);
        return $this->concatIds($profiles);
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
