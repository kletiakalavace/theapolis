<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationSearchType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchPhrase', TextType::class, array(
                    'required' => false,
                    'label' => 'organization.search.label.keyword',
                    'attr' => array('class' => 'form-control no-border'
                    ),
                )
            )
            ->add('location', TextType::class, array(
                    'required' => false,
                    'label' => 'organization.search.label.location',
                    'attr' => array('class' => 'form-control form-control-location no-border',
                        'placeholder' => 'organization.search.placeholder.location',
                    ),
                )
            )
            ->add('area', ChoiceType::class, array(
                    'label' => 'organization.search.label.distance',
                    'attr' => array('class' => 'form-control no-border',
                    ),
                    'choices' => array(
                        '10 Km' => '10km',
                        '50 Km' => '50km',
                        '100 Km' => '100km',
                        '150 Km' => '150km',
                    ),
                    'empty_value' => false,
                    'choices_as_values' => true,
                )
            )
            ->add('organizationKind', 'entity', array(
                    'class' => 'TheaterjobsInserateBundle:OrganizationKind',
                    'property' => 'name',
                    'expanded' => true,
                    'multiple' => true
                )
            )
            ->add('organizationSection', 'entity', array(
                    'class' => 'TheaterjobsInserateBundle:OrganizationSection',
                    'property' => 'name',
                    'expanded' => true,
                    'multiple' => true
                )
            )
            ->add('favorite', HiddenType::class)
            ->add('organization', HiddenType::class)
            ->add('tags', HiddenType::class);

        if ($options['role']) {
            $builder->add('status', ChoiceType::class,
                [
                    'choices' => [
                        'organization.status.pending' => 1,
                        'organization.status.active' => 2,
                        'organization.status.unknown' => 3,
                        'organization.status.closed' => 4
                    ],
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => true
                ]
            );
        }

        $builder->add('page', HiddenType::class);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\InserateBundle\Model\OrganizationSearch',
            'translation_domain' => 'messages',
            'role' => null,
            'isAnon' => true
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization_search';
    }
}