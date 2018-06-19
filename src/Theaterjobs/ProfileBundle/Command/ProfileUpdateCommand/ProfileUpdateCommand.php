<?php

namespace Theaterjobs\ProfileBundle\Command\ProfileUpdateCommand;


use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * Notifies users that have not updated profile for more than 100 days
 * Class ProfileUpdateCommand
 * @package Theaterjobs\ProfileBundle\Command\ProfileUpdateCommand
 */
class ProfileUpdateCommand extends ContainerAwareCommand
{
    use ContainerTrait;

    /** @var  EntityManager */
    protected $em;

    /** @var  OutputInterface */
    protected $output;


    protected function configure()
    {
        $this->setName('theaterjobs:cron:profile-update:notifications')
            ->addArgument('userIds', InputArgument::REQUIRED, 'User Ids to process')
            ->setDescription('Notify user that his profile is older than 100 days');
    }

    /**
     * Notifies users that have not updated profile for more than 100 days
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->em = $this->get('doctrine.orm.entity_manager');
        $ids = $input->getArgument('userIds');

        $profileIds = json_decode($ids);
        $profiles = $this->em->getRepository(Profile::class)->findById($profileIds);
        $this->sendNotifications($profiles);
    }

    /**
     * Send notification to users
     *
     * @param Profile[] $profiles
     *
     * @return void
     */
    protected function sendNotifications($profiles)
    {
        $type = 'profile_old_update';
        $i = 0;
        foreach ($profiles as $profile) {
            $user = $profile->getUser();
            $nr = $this->em->getRepository(Notification::class)->findByUserType($user->getId(), $type);
            if ($nr) {
                continue;
            }

            $this->output->writeln(sprintf("Sending notification to %s with id %d", $profile->getFullName(), $profile->getId()));
            $notification = new Notification();
            $notification->setTitle('dashboard.notification.profile.update.status.old')
                ->setCreatedAt(Carbon::now())
                ->setDescription('dashboard.notification.profile.update.status.old.description')
                ->setLink('tj_profile_profile_show')
                ->setLinkKeys(['slug' => $profile->getSlug()])
                ->setRequireAction(true);
            $profile->setOldProfile(true);

            $event = (new NotificationEvent())
                ->setObjectClass(User::class)
                ->setObjectId($user->getId())
                ->setNotification($notification)
                ->setUsers($user)
                ->setType('profile_old_update')
                ->setFlush(false);

            $this->getContainer()->get('event_dispatcher')->dispatch('notification', $event);
            ++$i;
        }
        try {
            $this->em->beginTransaction();
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->output->writeln("Error: " . $e->getMessage());
            $this->output->writeln("Trace: " . $e->getTraceAsString());
        }

        $this->output->writeln(sprintf("Sent Notification to %d users", $i));
    }
}