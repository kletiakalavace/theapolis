<?php

namespace Theaterjobs\ProfileBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\ProfileBundle\Entity\Creator;
use Theaterjobs\ProfileBundle\Entity\Director;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Entity\ProductionParticipations;
use Psr\Log\LoggerInterface;

/**
 * ProductionIndexListener Listener
 * The listener is used to update the nested mapping of the profile,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class ProductionIndexListener extends Listener implements EventSubscriber
{
    use ScheduleUpdateESIndexTrait;

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
     * Used to disable the listener when we generate dummy data (command theaterjobs:PrepareDB)
     *
     * @var bool
     */
    protected $prepareDB = false;

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
        return ['postPersist', 'postUpdate', 'preRemove', 'preFlush', 'postFlush'];
    }


    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        // get the command to be executed
        $command = $event->getCommand();

        if ($command->getName() === 'theaterjobs:PrepareDB') {
            $this->prepareDB = true;
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

            if ($entity instanceof ProductionParticipations) {
                $production = $entity->getProduction();
                if ($add) {
                    $production->addParticipation($entity);
                } else {
                    $production->removeParticipation($entity);
                }
                $this->updateIndex($production);
            }

            if ($entity instanceof Director) {
                $nameClass = Director::class;
                $ids = $this->em->getRepository($nameClass)->getDirectorProductionIds($entity->getId());
                if (count($ids) > 0) {
                    $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
                }
            }

            if ($entity instanceof Creator) {
                $nameClass = Creator::class;
                $ids = $this->em->getRepository($nameClass)->getCreatorProductionIds($entity->getId());
                if (count($ids) > 0) {
                    $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
                }
            }
        }
    }

    /**
     * @param Production $production
     */
    private function updateIndex(Production $production = null)
    {
        if ($this->objectPersister->handlesObject($production)) {
            if ($this->isObjectIndexable($production)) {
                $this->scheduledForUpdate[] = $production;
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