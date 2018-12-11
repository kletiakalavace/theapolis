<?php

namespace Theaterjobs\ProfileBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\ProfileBundle\Entity\Creator;

/**
 * Description of ProfessionCategoryTransformer
 *
 */
class CreatorTransformer implements DataTransformerInterface
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
            $choices[] = $object->getName();
        }

        return implode(',',$choices);
    }


    public function reverseTransform($values)
    {
        if (!$values) return array();

        $array = array();
        $creators = explode(",", $values);

        foreach ($creators as $creator)
        {
            $creatorDb = $this->om
                ->getRepository('TheaterjobsProfileBundle:Creator')
                ->findOneBy(array('name' => $creator));
            if(null === $creatorDb){
                $newCreator = new Creator();
                $newCreator->setName($creator);
                $this->om->persist($newCreator);
                $this->om->flush();

                $creatorDb = $newCreator;
            }
            $array[] = $creatorDb;
        }

        return $array;
    }
}
