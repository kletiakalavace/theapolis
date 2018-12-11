<?php


namespace Theaterjobs\UserBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\UserBundle\Entity\Notification;

/**
 * Deletes notificaitons older than 10 days
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class NotificationExpirationCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait, ScheduleUpdateESIndexTrait;

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:expirationNotification')
            ->setDescription('Command for deleting notifications older than 10 days');
    }

    /**
     * Delete notification older than 10 days
     *
     * @param InputInterface  $input  user input
     * @param OutputInterface $output terminal interface
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->get("doctrine.orm.entity_manager");
        $before10days = Carbon::today()->addDays(10);
        $notificationIds = $this->em->getRepository(Notification::class)->olderThanIds($before10days);

        $this->scheduleESIndex(UpdateESIndexCommand::DELETE, Notification::class, $notificationIds, 'cron', true);

        $output->writeln(sprintf("Removed %d notifications", count($notificationIds)));
    }
}
