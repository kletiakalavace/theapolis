<?php

namespace Theaterjobs\ProfileBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\ProfileBundle\Entity\Director;

/**
 * Description of ProfessionCategoryTransformer
 *
 */
class DirectorTransformer implements DataTransformerInterface
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
        $directors = explode(",", $values);

        foreach ($directors as $director)
        {
            $directorDb = $this->om
                ->getRepository('TheaterjobsProfileBundle:Director')
                ->findOneBy(array('name' => $director));
            if(null === $directorDb){
                $newDirector = new Director();
                $newDirector->setName($director);
                $this->om->persist($newDirector);
                $this->om->flush();

                $directorDb = $newDirector;
            }
            $array[] = $directorDb;
        }

        return $array;
    }
}
