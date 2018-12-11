<?php

namespace Theaterjobs\InserateBundle\Form\DataTransformer;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class OrganizationTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function transform($organization)
    {
        if (null === $organization) {
            return "";
        }

        return $organization->getName();
    }

    /**
     * Transforms a string  to an object (organization).
     * @param  string $organization
     * @return \Theaterjobs\InserateBundle\Entity\Organization
     * @throws TransformationFailedException if object (organization) is not found.
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reverseTransform($organization)
    {
        if (!$organization) {
            return null;
        }

        $orga = $this->em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name' => $organization));

        if (null === $orga) {
            $newOrg = new \Theaterjobs\InserateBundle\Entity\Organization();
            $newOrg->setCreatedAt(Carbon::now());
            $newOrg->setName($organization);
            $newOrg->setIsVisibleInList(true);
            $newOrg->setStatus(1);
            $this->em->persist($newOrg);
            $this->em->flush();
            return $newOrg;
        }
        return $orga;
    }
}
