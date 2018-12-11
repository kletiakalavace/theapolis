<?php

namespace Theaterjobs\ProfileBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\ProfileBundle\Entity\Skill;

/**
 * Description of ProfessionCategoryTransformer
 *
 */
class SkillTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($skill)
    {
        if (null === $skill) {
            return "";
        }

        return $skill->getTitle();
    }

    /**
     * Transforms a string  to an object (organization).
     *
     * @param  string $skill
     *
     * @return Organization|null|mixed
     *
     * @throws TransformationFailedException if object (organization) is not found.
     */
    public function reverseTransform($skill)
    {
        if (!$skill) {
            return null;
        }

        $name = $skill;

        $skill = $this->om
            ->getRepository('TheaterjobsProfileBundle:Skill')
            ->findOneBy(array('title' => $name));


        if (null === $skill) {
            $newSkill = new Skill();
            $newSkill->setTitle($name);
            $newSkill->setIsLanguage(true);
            $this->om->persist($newSkill);
            $this->om->flush();
            return $newSkill;
        }
        return $skill;
    }
}
