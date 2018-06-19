<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class OccupationType
 * @package Theaterjobs\ProfileBundle\Form\Type
 */
class OccupationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roleName', TextType::class, array(
                    'required' => false,
                    'label' => 'people.edit.label.occupationRolename',
                    'attr' => array(
                        'maxlength' => 50,
                    )
                )
            )
            ->add('assistant', CheckboxType::class, array(
                    'label' => 'people.edit.label.occupationAssistant',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                    'attr' => array(
                        'maxlength' => 50,
                    )
                )
            )
            ->add('management', CheckboxType::class, array(
                    'label' => 'people.edit.label.occupationManagement',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                    'attr' => array(
                        'maxlength' => 50,
                    )
                )
            )
            ->add('description', TextareaType::class, array(
                    'label' => 'people.edit.label.occupationDescription',
                    'required' => false,
                    'attr' => array(
                        'class' => 'description-modalproduction',
                        'maxlength' => 190,
                        'rows' => 1,
                    )
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Occupation',
            'translation_domain' => 'messages'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'theaterjobs_profilebundle_occupation';
    }
}
