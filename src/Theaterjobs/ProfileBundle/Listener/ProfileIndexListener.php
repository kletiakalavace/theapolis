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
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Theaterjobs\MainBundle\Command\ScheduleUpdateESIndexTrait;
use Theaterjobs\MainBundle\Command\UpdateESIndexCommand;
use Theaterjobs\ProfileBundle\Entity\Experience;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\Production;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Psr\Log\LoggerInterface;
use Theaterjobs\ProfileBundle\Entity\Qualification;
use Theaterjobs\UserBundle\Entity\User;

/**
 * ProfileIndexListener Listener
 * The listener is used to update the nested mapping of the profile,
 * the bundle doesn't support the update of them in such deep level of the tree.
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class ProfileIndexListener extends Listener implements EventSubscriber
{
    use ScheduleUpdateESIndexTrait;

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
     * @var EntityManager
     */
    protected $em;

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

            if ($entity instanceof Experience) {
                $profile = $entity->getProfile();

                if ($profile) {
                    if ($add) {
                        $profile->addExperience($entity);
                    } else {
                        $profile->removeExperience($entity);
                    }
                    $this->updateIndex($profile);
                }
            }

            if ($entity instanceof Production) {
                $nameClass = Production::class;
                $ids = $this->em->getRepository($nameClass)->getProductionProfileIds($entity->getId());
                $this->scheduleESIndex(UpdateESIndexCommand::UPDATE, $nameClass, $ids, 'app', true);
            }

            if ($entity instanceof Qualification) {
                $qualificationSection = $entity->getQualificationSection();
                $profile = $qualificationSection->getProfile();

                if ($qualificationSection && $profile) {
                    if ($add) {
                        $qualificationSection->addQualification($entity);
                    } else {
                        $qualificationSection->removeQualification($entity);
                    }
                    $this->updateIndex($profile);
                }
            }

            if ($entity instanceof MediaImage) {
                $profile = $entity->getProfile();

                if ($profile) {
                    if ($add) {
                        $profile->addMediaImage($entity);
                    } else {
                        $profile->removeMediaImage($entity);
                    }
                    $this->updateIndex($profile);
                }
            }

            if ($entity instanceof User) {
                $profile = $entity->getProfile();

                if ($profile) {
                    $this->updateIndex($profile);
                }
            }
        }
    }

    /**
     * @param Profile $profile
     */
    private function updateIndex(Profile $profile = null)
    {
        if ($this->objectPersister->handlesObject($profile)) {
            if ($this->isObjectIndexable($profile)) {
                $this->scheduledForUpdate[] = $profile;
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