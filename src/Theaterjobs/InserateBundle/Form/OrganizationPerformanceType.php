<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationPerformanceType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationPerformanceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('season', 'season_choice', array(
                'label' => 'organization.edit.performance.label.season',
                'attr' => array('class' => 'performance_season')
            ))
            ->add('performanceNumber', 'text', array(
                'label' => 'organization.edit.performance.label.number',
                'attr' => array('class' => 'performanceNumber',
                    'min' => 0,
                    'max' => 100000000
                ),
                'constraints' => [
                    new Assert\Type('digit'),
                    new Assert\Range(['min' => 0, 'max' => 100000000])
                ]
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\OrganizationPerformance'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_organization_performance_type';
    }

}
