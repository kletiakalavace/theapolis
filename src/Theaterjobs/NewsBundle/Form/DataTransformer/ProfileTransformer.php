<?php

namespace Theaterjobs\NewsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * Description of ProfessionCategoryTransformer
 *
 */
class ProfileTransformer implements DataTransformerInterface
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

    public function transform($values)
    {
        if ($values === null) return array();
        $choices = array();
        foreach ($values as $object)
        {
            $choices[] = $object->getId();
        }

        return implode(',',$choices);
    }


    public function reverseTransform($values)
    {
        if (!$values) return array();

        $users = explode(",", $values);

        $profiles = $this->om
            ->getRepository('TheaterjobsProfileBundle:Profile')
            ->findById($users);

        return $profiles;
    }
}
