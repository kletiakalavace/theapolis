<?php
namespace Theaterjobs\InserateBundle\Listener;

use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Doctrine\Listener;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Theaterjobs\InserateBundle\Entity\Organization;

/**
 * @TODO Marlind explain the case when we use it here
 * @author   Marlind Parllaku <marlind93@gmail.com>
 */
class OrganizationCreateSubscriber extends Listener implements EventSubscriber
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Organization) {
            $entity->setUser( ($this->tokenStorage->getToken())?$this->tokenStorage->getToken()->getUser():null);
        }

    }
}