<?php

namespace Theaterjobs\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Carbon\Carbon;

/**
* @DI\FormType
*/
class SeasonType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    	$dates = [];
    	$start = Carbon::now();
    	$end   = Carbon::createFromDate(2000, 1, 1);

    	while ( $start->gt($end) )
    	{
    		$season =  $start->year . '/' . $start->addYear()->year;
    		$dates[$season] = $season;
    		$start = $start->subYear()->subYear();
    	}

        $resolver->setDefaults(array(
            'choices' => $dates
            ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'season_choice';
    }
}