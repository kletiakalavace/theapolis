<?php

namespace Theaterjobs\AdminBundle\Command;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Carbon\Carbon;
use Theaterjobs\AdminBundle\Entity\VioReminder;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;

/**
 *  Put vio record in reminder so the admin can be notified to take action
 *
 * @package Theaterjobs\AdminBundle\Command
 *
 * @author Igli Hoxha <igliihoxhan@gmail.com>
 */

class VioReminderCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight;

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:vio:reminder')
            ->setDescription('Command that put the vio record to reminder if interval hit 0')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'Simulate a day'
            );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get time in sec when the cron start
        $startScript = microtime(true);

        $em = $this->getContainer()->get('doctrine')->getManager();

        if ($input->getOption('date')) {
            $today = new Carbon($input->getOption('date'));
        } else {
            $today = Carbon::today();
        }

        // get vio that aren't on the reminder list
        $vio = $em->getRepository('TheaterjobsAdminBundle:Vio')
            ->findBy(['isChecked' => false]);

        // create count for the recodes that will be put on reminder
        $count = 0;

        foreach ($vio as $item) {
            $created = new Carbon($item->getCreatedAt()->format('Y-m-d'));

            // check if the record is due to be listed on the reminder record
            if ($today->diffInDays($created) >= $item->getDaysInterval()) {
                $vioReminder = new VioReminder();
                $vioReminder->setVio($item);
                $item->setIsChecked(true);
                $em->persist($item);
                $em->persist($vioReminder);
                $em->flush();

                $count++;
            }
        }

        $output->writeln("Cron for vio took " . round((microtime(true) - $startScript), 3) . " sec for $count records");
    }
}
