<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationVisitorsType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationVisitorsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('season', 'season_choice', array(
                'label' => 'organization.edit.visitors.label.season',
                'attr' => array('class' => 'visitors_season')
            ))
            ->add('visitorsNumber', 'text', array(
                    'label' => 'organization.edit.visitors.label.number',
                    'attr' => array('class' => 'visitorsNumber',
                        'min' => 0,
                        'max' => 100000000
                    ),
                    'constraints' => [
                        new Assert\Type('digit'),
                        new Assert\Range(['min' => 0, 'max' => 100000000])
                    ]
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\OrganizationVisitors'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_organization_visitors_type';
    }

}
