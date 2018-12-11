<?php

namespace Theaterjobs\InserateBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Jobmail;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * JobsIndexListener Listener
 * The listener is used to update the nested mapping of the jobs,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class JobsIndexListener extends Listener implements EventSubscriber
{
    use ScheduleUpdateESIndexTrait;

    const PROFILE_FIELDS = ['subtitle'];
    const ORGANIZATION_FIELDS = ['path', 'name'];

    /**
     * Object persister.
     *
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var IndexableInterface
     */
    private $indexable;

    /**
     * Configuration for the listener.
     *
     * @var array
     */
    private $config;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Used to trigger the listener update only if predefined fields have changed
     *
     * @var bool
     */
    protected $changedFields = false;

    /**
     * Used to disable the listener when we generate dummy data (command theaterjobs:PrepareDB)
     *
     * @var bool
     */
    protected $prepareDB = false;


    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        // get the command to be executed
        $command = $event->getCommand();

        if ($command->getName() === 'theaterjobs:PrepareDB') {
            $this->prepareDB = true;
        }

    }


    /**
     * NewsIndexListener constructor.
     * @param ObjectPersisterInterface $postPersister
     * @param IndexableInterface $indexable
     * @param array $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectPersisterInterface $postPersister,
        IndexableInterface $indexable,
        $config = [],
        LoggerInterface $logger
    )
    {
        $this->objectPersister = $postPersister;
        $this->indexable = $indexable;
        $this->config = array_merge([
            'identifier' => 'id',
        ], $config);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($logger && $this->objectPersister instanceof ObjectPersister) {
            $this->objectPersister->setLogger($logger);
        }
        parent::__construct($postPersister, $indexable, $config, $logger);
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postPersist', 'postUpdate', 'preRemove', 'preFlush', 'postFlush', 'preUpdate'];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->em = $eventArgs->getEntityManager();

        if ($entity instanceof Profile) {
            // check if the predefined fields of profile has changed
            foreach (self::PROFILE_FIELDS as $field) {
                if ($eventArgs->hasChangedField($field)) {
                    $this->changedFields = true;
                }
            }
        }

        if ($entity instanceof Organization) {
            // check if the predefined fields of profile has changed
            foreach (self::ORGANIZATION_FIELDS as $field) {
                if ($eventArgs->hasChangedField($field)) {
                    $this->changedFields = true;
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->em = $eventArgs->getEntityManager();

        $this->handleChild($entity);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->em = $eventArgs->getEntityManager();

        $this->handleChild($entity);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->em = $eventArgs->getEntityManager();

        $this->handleChild($entity, false);
    }

    /**
     * @param $entity
     * @param bool $add
     */
    private function handleChild($entity, $add = true)
    {
        // es update disabled for dummy data
        if (!$this->prepareDB) {

            if ($entity instanceof Jobmail) {
                $job = $entity->getJob();

                if ($add) {
                    $job->addJobmail($entity);
                } else {
                    $job->removeJobmail($entity);
                }

                $this->updateIndex($job);
            }

            if ($entity instanceof Profile) {
                $user = $entity->getUser();
                // update job es index only if the predefined fields in preUpdate event have changed
                if ($this->changedFields && $user) {
                    $nameClass = Job::class;
                    $ids = $this->em->getRepository($nameClass)->getUserJobIds($user->getId());
                    $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
                }
            }

            if ($entity instanceof MediaImage) {
                // update job es index only for profile photo
                if ($entity->getIsProfilePhoto()) {
                    $user = $entity->getProfile()->getUser();
                    if ($user) {
                        $nameClass = Job::class;
                        $ids = $this->em->getRepository($nameClass)->getUserJobIds($user->getId());
                        $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
                    }
                }
            }

            if ($entity instanceof Organization) {
                // update job es index only if the predefined fields in preUpdate event have changed
                if ($this->changedFields) {
                    $nameClass = Organization::class;
                    $ids = $this->em->getRepository($nameClass)->getOrganizationJobIds($user->getId());
                    $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
                }
            }
        }
    }

    /**
     * @param Job $job
     */
    private function updateIndex(Job $job = null)
    {
        if ($this->objectPersister->handlesObject($job)) {
            if ($this->isObjectIndexable($job)) {
                $this->scheduledForUpdate[] = $job;
            }
        }
    }


    /**
     * @param object $object
     * @return bool
     */
    private function isObjectIndexable($object)
    {
        return $this->indexable->isObjectIndexable(
            $this->config['index'],
            $this->config['type'],
            $object
        );
    }

}