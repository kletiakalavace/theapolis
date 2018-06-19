<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonalDataType
 * @package Theaterjobs\ProfileBundle\Form
 */
class PersonalDataType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthDate', 'date', array(
                    'label' => 'people.edit.label.birthday',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => false,
                    'constraints' => [ new Assert\Date() ]
                )
            )
            ->add('birthPlace', 'text', [
                'label' => 'people.edit.label.birth_place',
                'required' => false,
                'attr' => [ 'maxlength' => 35 ]
                ]
            )
            ->add('nationality', ChoiceType::class, array(
                    'label' => 'people.edit.label.nationality',
                    'choices' => [
                        'EU' => 1,
                        'NOT EU' => 0
                    ],
                    'choices_as_values' => true,
                    'empty_value' => 'people.edit.noSelect.nationality',
                    'required' => false,
                    'multiple' => false,
                )
            )
            ->add('ageRoleFrom', 'integer', array(
                'label' => 'people.edit.label.ageRoleFrom',
                'max_length' => 2,
                'attr' => [
                    'min' => 1,
                    'max' => 99,
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Range(['min' => 1, 'max' => 99])
                ]
            ))
            ->add('ageRoleTo', 'integer', array(
                'label' => 'people.edit.label.ageRoleTo',
                'max_length' => 2,
                'attr' => [
                    'min' => 1,
                    'max' => 99,
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Range(['min' => 1, 'max' => 99])
                ]
            ))
            ->add('height', 'integer', array(
                'label' => 'people.edit.label.height',
                'max_length' => 3,
                'attr' => [
                    'min' => 50,
                    'max' => 250,
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Range(['min' => 50, 'max' => 250])
                ]
            ))
            ->add('shoeSize', 'integer', array(
                'label' => 'people.edit.label.shoeSize',
                'max_length' => 2,
                'attr' => [
                    'min' => 30,
                    'max' => 60,
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Type('integer'),
                    new Assert\Range(['min' =>30, 'max' => 60])
                ]

            ))
            ->add('clothesSize', 'integer', array(
                'label' => 'people.edit.label.clotheSize',
                'max_length' => 2,
                'attr' => [
                    'min' => 20,
                    'max' => 200,
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Type('integer'),
//                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 20, 'max' => 200])
                ]
            ))
            ->add('eyeColor', 'entity', array(
                'label' => 'people.edit.label.eyeColor',
                'class' => 'TheaterjobsProfileBundle:EyeColor',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'people.edit.noSelect.chooseEye',
            ))
            ->add('hairColor', 'entity', array(
                'label' => 'people.edit.label.hairColor',
                'class' => 'TheaterjobsProfileBundle:HairColor',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'people.edit.noSelect.chooseHair',
                'attr' => [
                ]
            ))
            ->add('voiceCategories', 'theaterjobs_category_category', array(
                    'required' => false,
                    'label' => 'people.edit.label.voiceCategories',
                    'choice_list' => $options['voice_category_choice_list'],
                    'attr' => array(
                        'class' => 'voiceCategories select-empty-value placeholder-entercharact',
                        'placeholder' => 'people.edit.noSelect.voiceCategory'
                    ),
                    'empty_value' => 'people.edit.noSelect.voiceCategory',
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\PersonalData',
            'translation_domain' => 'forms',
            'voice_category_choice_list' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_personaldata';
    }
}
