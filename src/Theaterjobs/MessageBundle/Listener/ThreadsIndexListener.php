<?php

namespace Theaterjobs\MessageBundle\Listener;

use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Psr\Log\LoggerInterface;
use Theaterjobs\MessageBundle\Entity\Message;
use Theaterjobs\MessageBundle\Entity\ThreadMetadata;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;

/**
 * ThreadsIndexListener Listener
 * The listener is used to update the nested mapping of the thread,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class ThreadsIndexListener extends Listener implements EventSubscriber
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var IndexableInterface
     */
    private $indexable;
    /**
     * @var array
     */
    private $config;

    /**
     * ThreadsIndexListener constructor.
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

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        $this->handleChild($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $this->handleChild($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $this->handleChild($entity, false);
    }

    /**
     * @param $entity
     */
    private function handleChild($entity)
    {
        if ($entity instanceof Profile) {
            $user = $entity->getUser();
            if ($user) {
                $metadata = $user->getMetadataThreads();
                foreach ($metadata as $metadatum) {
                    $this->updateIndex($metadatum->getThread());
                }
            }

        }
        if ($entity instanceof Message) {
            $thread = $entity->getThread();
            $this->updateIndex($thread);
        }
        if ($entity instanceof ThreadMetadata) {
            $thread = $entity->getThread();
            $this->updateIndex($thread);
        }
    }

    /**
     * @param $obj
     */
    private function updateIndex($obj = null)
    {
        if ($this->objectPersister->handlesObject($obj)) {
            if ($this->isObjectIndexable($obj)) {
                $this->scheduledForUpdate[] = $obj;
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