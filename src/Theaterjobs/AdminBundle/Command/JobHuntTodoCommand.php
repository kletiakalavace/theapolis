<?php

namespace Theaterjobs\AdminBundle\Command;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Carbon\Carbon;
use Theaterjobs\AdminBundle\Entity\JobHuntToDo;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;

/**
 *  Put vio record in reminder so the admin can be notified to take action
 *
 * @package Theaterjobs\AdminBundle\Command
 *
 * @author Igli Hoxha <igliihoxhan@gmail.com>
 */
class JobHuntTodoCommand extends ContainerAwareCommand implements CronCommand
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
            ->setName('theaterjobs:cron:job:hunt:todo')
            ->setDescription('Command that put the jobHunt record to todo if interval hit 0')
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

        // get jobs hunt that aren't on the jobtodo  list
        $jobsHunt = $em->getRepository('TheaterjobsAdminBundle:JobHunt')
            ->findBy(['isChecked' => false, 'priority' => [1, 2, 3]]);

        // create count for the recodes that will be put on reminder
        $count = 0;

        foreach ($jobsHunt as $item) {
            $created = new Carbon($item->getCreatedAt()->format('Y-m-d'));

            // check if the record is due to be listed on the jobtodo record
            if ($today->diffInDays($created) >= $item->getIntervalDays()) {
                $jobHuntTodo = new JobHuntToDo();
                $jobHuntTodo->setJobHunt($item);
                $item->setIsChecked(true);
                $em->persist($item);
                $em->persist($jobHuntTodo);
                $count++;
            }
        }
        $em->flush();

        $output->writeln("Cron for job hunt took " . round((microtime(true) - $startScript), 3) . " sec for $count records");
    }
}
