<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class PeopleSearchType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class PeopleSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchPhrase', TextType::class,
                [
                    'required' => false,
                    'label' => 'people.search.label.keyword',
                    'attr' =>
                        [
                            'class' => 'form-control no-border'
                        ]
                ]
            )
            ->add('location', TextType::class,
                [
                    'required' => false,
                    'label' => 'people.search.label.location',
                    'attr' =>
                        [
                            'class' => 'form-control form-control-location no-border',
                            'placeholder' => 'people.search.placeholder.location'
                        ]
                ]
            )
            ->add('organization', HiddenType::class)
            ->add('subcategories', ChoiceType::class, [
                    'choices' => $options['subcategories'],
                    'expanded' => true,
                    'multiple' => true
                ]
            )
            ->add('area', ChoiceType::class, [
                    'label' => 'people.search.label.distance',
                    'attr' =>
                        [
                            'class' => 'form-control no-border'
                        ]
                    ,
                    'choices' =>
                        [
                            '10 Km' => '10km',
                            '50 Km' => '50km',
                            '100 Km' => '100km',
                            '150 Km' => '150km',
                        ],
                    'empty_value' => false,
                    'choices_as_values' => true
                ]
            );
        // show published filter only for admins
        if ($options['isAdmin']) {
            $builder->add('published', ChoiceType::class,
                [
                    'choices' =>
                        [
                            'Published' => true,
                            'Unpublished' => false
                        ]
                    ,
                    'empty_value' => false,
                    'data' => true,
                    'choices_as_values' => true,
                ]
            );
        }
        // show favorite filter only for logged users
        if ($options['isLogged']) {
            $builder->add('favorite', HiddenType::class);
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
            'data_class' => 'Theaterjobs\ProfileBundle\Model\PeopleSearch',
            'translation_domain' => 'forms',
            'isAdmin' => false,
            'isLogged' => false,
            'subcategories' => []
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'people_search_type';
    }
}