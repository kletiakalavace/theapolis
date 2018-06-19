<?php

namespace Theaterjobs\MembershipBundle\Command;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\UserBundle\Entity\User;
use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Class GenerateBillingsCommand
 * @package Theaterjobs\MembershipBundle\Command
 * @author Jurgen Rexhmati
 */
class GenerateBillingsCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:mmembership:generate-billing')
            ->setDescription('Extend membership for recurring users and generate billing');
    }

    /**
     * Generates a billing for users that have recurring payments and they membership has run out
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $em = $this->get("doctrine.orm.entity_manager");
            $batchSize = 50;
            // Recurring Users
            $users = $em->getRepository(User::class)->getRecurringUsersExpireToday();
            $i = 0;
            foreach ($users as $user) {
                $job = new JobQueue('app:create:billing', [$user->getId()], true, "cron", JobQueue::PRIORITY_HIGH);
                $em->persist($job);
                if ($i % $batchSize === 0) {
                    $em->flush();
                    $em->clear();
                }
                ++$i;
            }
            $em->flush();
            // Generate billings
            $output->writeln(sprintf("%d Jobs loaded to queue", $i));

        } catch (\Exception $e) {
            $output->writeln("Error: " . $e->getMessage());
            $output->writeln("Trace: " . $e->getTraceAsString());
        }
    }
}
