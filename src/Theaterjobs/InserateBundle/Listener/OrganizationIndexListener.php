<?php

namespace Theaterjobs\InserateBundle\Listener;

use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Theaterjobs\InserateBundle\Entity\AdminComments;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\ProfileBundle\Entity\Experience;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\UserBundle\Entity\UserOrganization;

/**
 * OrganizationIndexListener Listener
 * The listener is used to update the nested mapping of the organization,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class OrganizationIndexListener extends Listener implements EventSubscriber
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

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        $this->handleChild($entity);
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
            if ($entity instanceof AdminComments) {
                if ($entity->getOrganization()) {
                    $organization = $entity->getOrganization();

                    if ($add) {
                        $organization->addAdminComment($entity);
                    } else {
                        $organization->removeAdminComment($entity);
                    }

                    $this->updateIndex($organization);
                }
            }

            if ($entity instanceof Experience) {
                $organization = $entity->getOrganization();

                if ($add) {
                    $organization->addExperience($entity);
                } else {
                    $organization->removeExperience($entity);
                }
                $this->updateIndex($organization);
            }

            if ($entity instanceof Production) {
                $organization = $entity->getOrganizationRelated();

                if ($organization) {
                    if ($add) {
                        $organization->addProduction($entity);
                    } else {
                        $organization->removeProduction($entity);
                    }
                    $this->updateIndex($organization);
                }

            }

            if ($entity instanceof UserOrganization) {
                $organization = $entity->getOrganization();

                if ($add) {
                    $organization->addUserOrganization($entity);
                } else {
                    $organization->removeUserOrganization($entity);
                }

                $this->updateIndex($organization);
            }
        }

    }

    /**
     * @param Organization $organization
     */
    private function updateIndex(Organization $organization = null)
    {
        if ($this->objectPersister->handlesObject($organization)) {
            if ($this->isObjectIndexable($organization)) {
                $this->scheduledForUpdate[] = $organization;
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