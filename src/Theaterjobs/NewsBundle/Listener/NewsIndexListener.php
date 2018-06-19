<?php

namespace Theaterjobs\NewsBundle\Listener;

use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Theaterjobs\NewsBundle\Entity\News;
use Psr\Log\LoggerInterface;
use Theaterjobs\NewsBundle\Entity\Replies;
use Theaterjobs\NewsBundle\Model\ProfileInterface;

/**
 * NewsIndexListener Listener
 * The listener is used to update the nested mapping of the news,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class NewsIndexListener extends Listener implements EventSubscriber
{
    /**
     * Object persister.
     *
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * @var PropertyAccessor
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
     * Used to disable the listener when we generate dummy data (command theaterjobs:PrepareDB)
     *
     * @var bool
     */
    private $prepareDB = false;


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

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        // get the command to be executed
        $command = $event->getCommand();

        if ($command->getName() === 'theaterjobs:PrepareDB') {
            $this->prepareDB = true;
        }

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
     * @param bool $add
     */
    private function handleChild($entity, $add = true)
    {
        // es update disabled for dummy data
        if (!$this->prepareDB) {

            if ($entity instanceof Replies) {
                $news = $entity->getNews();

                if ($add) {
                    $news->addReply($entity);
                } else {
                    $news->removeReply($entity);
                }

                $this->updateIndex($news);
            }

            if ($entity instanceof ProfileInterface) {
                $news = $entity->getNews();

                if ($add) {
                    $news->addUser($entity);
                } else {
                    $news->removeUser($entity);
                }
                $this->updateIndex($news);
            }
        }

    }

    /**
     * @param News $news
     */
    private function updateIndex(News $news = null)
    {
        if ($this->objectPersister->handlesObject($news)) {
            if ($this->isObjectIndexable($news)) {
                $this->scheduledForUpdate[] = $news;
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