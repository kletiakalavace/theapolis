<?php

namespace Theaterjobs\InserateBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Theaterjobs\InserateBundle\Entity\Job;

/**
 * Transform string to class for form generator mapping
 *
 * Class InserateTransformer
 * @package Theaterjobs\InserateBundle\Form\DataTransformer
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class JobTransformer implements DataTransformerInterface
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * JobTransformer constructor.
     * @param EntityManager $em
     *
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Transform a class into string
     *
     * @param Job $job
     * @return string
     *
     */
    public function transform($job)
    {
        if (null === $job) {
            return "";
        }
        return $job->getSlug();
    }

    /**
     * Transforms a string  to an object (Job).
     *
     * @param  string $job
     *
     * @return \Theaterjobs\InserateBundle\Entity\Job
     */
    public function reverseTransform($job)
    {
        if (!$job) {
            return null;
        }
        /** @var Job $jobObj */
        $jobObj = $this->em
            ->getRepository('TheaterjobsInserateBundle:Inserate')
            ->findOneBy(array('slug' => $job));
        if($jobObj){
            return $jobObj;
        }
        return null;
    }

}