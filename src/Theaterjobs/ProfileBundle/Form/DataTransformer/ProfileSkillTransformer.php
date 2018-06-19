<?php

namespace Theaterjobs\ProfileBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Theaterjobs\ProfileBundle\Entity\Skill;

/**
 * Description of ProfessionCategoryTransformer
 *
 */
class ProfileSkillTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;
    private $security;

    /**
     * @param ObjectManager $om
     * @param TokenStorage $security
     */
    public function __construct(ObjectManager $om, TokenStorage $security)
    {
        $this->om = $om;
        $this->security = $security;
    }


    public function transform($values)
    {
        if ($values === null) return array();
        $choices = array();
        foreach ($values as $object) {
            $choices[] = $object->getTitle();
        }

        return implode(',', $choices);
    }


    public function reverseTransform($values)
    {
        if (!$values) return array();

        $array = array();
        $creators = explode(",", $values);
        foreach ($creators as $creator) {
            $creatorDb = $this->om
                ->getRepository('TheaterjobsProfileBundle:Skill')
                ->findOneBy(array('title' => $creator));
            if (null === $creatorDb) {
                $newCreator = new Skill();
                $newCreator->setTitle($creator);
                $newCreator->setInserter($this->security->getToken()->getUser()->getProfile());
                $this->om->persist($newCreator);
                $this->om->flush();

                $creatorDb = $newCreator;
            }
            $array[] = $creatorDb;
        }

        return $array;
    }
}